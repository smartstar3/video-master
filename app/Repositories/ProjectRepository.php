<?php namespace MotionArray\Repositories;

use MotionArray\Models\Project;
use MotionArray\Models\User;
use MotionArray\Repositories\PortfolioRepository;
use MotionArray\Repositories\UploadableRepository;
use MotionArray\Repositories\PortfolioThemeRepository;
use MotionArray\Services\Slim;
use AWS;

class ProjectRepository extends UploadableRepository
{
    public $errors = [];

    private $portfolio;

    private $portfolioTheme;

    public function __construct(
        Project $project,
        PortfolioRepository $portfolio,
        PortfolioThemeRepository $portfolioTheme)
    {
        $this->model = $project;

        $this->portfolio = $portfolio;
        $this->portfolioTheme = $portfolioTheme;
    }

    public function findByPermalink($permalink)
    {
        return $this->model->where('permalink', '=', $permalink)->first();
    }

    public function findPublicProjectsByUser($userId = null)
    {
        return $this->findByUser($userId, function ($query) {
            return $query->where('is_public', true);
        });
    }

    public function findReviewsByUser($userId = null)
    {
        return $this->findByUser($userId, function ($query) {
            return $query->where('has_review', true);
        });
    }

    /**
     * @param null $userId
     * @param callable|null $modifier
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findByUser($userId = null, callable $modifier = null)
    {
        if (!$userId) {
            $userId = \Auth::id();
        }

        $query = $this->model->where('user_id', $userId);

        if ($modifier) {
            $query = $modifier($query);
        }

        return $query->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Save project and all relationships
     *
     * @param  array $attributes
     *
     * @return boolean
     */
    public function make(array $attributes)
    {
        // Set attributes
        $project = new Project;

        $project->user_id = (int)$attributes['user_id'];
        $project->preview_type = $attributes['type'];
        $project->product_status_id = 1; // Published
        $project->event_code_id = 1; // Ready
        $project->name = $attributes['name'];
        $project->description = $attributes['description'];

        if (isset($attributes['music_id'])) {
            $project->music_id = (int)$attributes['music_id'] === 0 ? null : (int)$attributes['music_id'];
        }

        if (isset($attributes['has_review'])) {
            $project->has_review = $attributes['has_review'];
        }

        if (isset($attributes['is_public'])) {
            $project->is_public = $attributes['is_public'];
        }

        // Save the project
        if ($project->save()) {

            // Set audio placeholder
            // TODO: This is temporary and needs removing eventually
            if ($project->preview_type == 'audio') {
                $project->audio_placeholder = $this->getRandomAudioPlaceholder();
                $project->save();
            }

            // Store tags
            if (isset($attributes['tags']) && $attributes['tags']) {
                $tags = $this->processTags($attributes['tags']);
                foreach ($tags as $tag) {
                    // FIXME: A null array item is creeping in for some reason
                    if (!is_null($tag)) {
                        $project->tags()->save($tag);
                    }
                }
            }

            return $project;
        }

        // Set any validation errors
        $this->errors = $project->errors;

        return false;
    }

    public function updateReviewSettings(Project $project, array $attributes)
    {
        if (array_key_exists("uses_password", $attributes) && array_key_exists("newpassword", $attributes)
            && $attributes['uses_password'] === false && !$attributes['newpassword']
        ) {
            $attributes['password'] = null;
            unset($attributes['newpassword']);
        }

        $project->reviewSettings()->updateOrCreate([], $attributes);
    }

    /**
     * Update project and any relationships
     *
     * @param  integer $id Project ID
     * @param  array $attributes
     *
     * @return boolean
     */
    public function update($projectId, array $attributes)
    {
        $project = $this->findById($projectId);

        // Set event code comparator
        $current_event_code = $project->event_code_id;

        $this->updateReviewSettings($project, array_only($attributes, ['uses_password', 'newpassword', 'allow_download']));

        if (isset($attributes['is_public'])) $project->is_public = (int)$attributes['is_public'];
        if (isset($attributes['has_review'])) $project->has_review = (int)$attributes['has_review'];

        // Set attributes
        if (isset($attributes['seller_id'])) $project->seller_id = (int)$attributes['seller_id'];
        if (isset($attributes['credit_seller'])) $project->credit_seller = (int)$attributes['credit_seller'];

        if (isset($attributes['product_status_id'])) {
            $project->product_status_id = (int)$attributes['product_status_id'];
        }

        if (isset($attributes['event_code_id'])) $project->event_code_id = (int)$attributes['event_code_id'];
        if (isset($attributes['name'])) {
            if ($project->name != $attributes['name']) {
                // Name has changed so we need to update the slug.
                $project->slug = $project->generateSlug($attributes['name']) . "-" . $project->id;
            }
            $project->name = $attributes['name'];
        }
        if (isset($attributes['description'])) $project->description = $attributes['description'];
        if (isset($attributes['audio_placeholder'])) $project->audio_placeholder = $attributes['audio_placeholder'];

        $this->upsertPreview($project, $attributes);

        if ($project->save()) {
            if ($current_event_code !== $project->event_code_id || $project->event_code_id == 2) {
                $this->eventHandler($project);
            }

            // Store tags
            if (isset($attributes['tags']) && $attributes['tags']) {
                $project->tags()->detach();

                $tags = $this->processTags($attributes['tags']);

                foreach ($tags as $tag) {
                    // FIXME: When a new tag is added a NULL entry in the array sneaks in which chokes the relationship save.
                    if (!is_null($tag)) {
                        $project->tags()->save($tag);
                    }
                }
            }

            return $project;
        }

        // Set any validation errors
        $this->errors = $project->errors;

        return false;
    }

    /**
     * Unpublishes the given project (from review and portfolio)
     * without removing it
     *
     * @param Project $project
     */
    public function unpublish(Project $project)
    {
        $project->is_public = false;
        $project->has_review = false;
        $project->save();
    }

    public function replaceImage(Project $project, $key, $value = null)
    {
        $this->deleteImage($project, $key);

        return $this->uploadImage($project, $key, $value);
    }

    public function deleteImage(Project $project, $key)
    {
        $file = $project->settings ? $project->settings[$key] : null;

        if (!$file) {
            return;
        }

        $s3 = AWS::get('s3');

        $bucket = Project::previewsBucket();

        $filename = basename($file);

        if ($file) {
            return $s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => $filename
            ]);
        }
    }

    public function uploadImage(Project $project, $key, $value = null)
    {
        $filename = 'rp' . $project->id . '-' . $key . '-' . time();

        $bucket = Project::previewsBucket();

        $url = Slim::uploadToAmazon($bucket, $filename, $value);

        return $url;
    }

    public function restoreProjects(User $user)
    {
        return $user->projects()
            ->onlyTrashed()
            ->has('previewUploads')
            ->restore();
    }
}

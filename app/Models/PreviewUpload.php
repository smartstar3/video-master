<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MotionArray\Jobs\SendProductToAlgolia;
use MotionArray\Models\Traits\Uploadable;

class PreviewUpload extends BaseModel
{
    use SoftDeletes;

    protected $guarded = [];

    protected $appends = ['availableResolutions'];

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    // todo: should restrict type? $this->uploadable->where('type', '=', 'project/product')
    public function project()
    {
        return $this->uploadable();
    }

    public function product()
    {
        return $this->uploadable();
    }

    public function uploadable()
    {
        return $this->morphTo();
    }

    public function files()
    {
        return $this->hasMany('MotionArray\Models\PreviewFile');
    }

    public function outputs()
    {
        return $this->hasMany('MotionArray\Models\Output');
    }

    public function videoFiles()
    {
        return $this->files()->video();
    }

    public function thumbnails()
    {
        return $this->files()->thumbnail();
    }

    public function comments()
    {
        return $this->hasMany('MotionArray\Models\ProjectComment');
    }

    /*
	|--------------------------------------------------------------------------
	| Accessors & Mutators
	|--------------------------------------------------------------------------
	*/
    public function getPlaceholderAttribute()
    {
        return $this->thumbnails()->where('id', '=', $this->placeholder_id)->first();
    }

    public function getAvailableResolutionsAttribute()
    {
        $heights = $this->files()
            ->where('label', '!=', PreviewFile::ORIGINAL)
            ->where('format', '=', 'mpeg4')
            ->groupBy('height')
            ->pluck('height', 'id');

        // Adding HD or SD string
        $heights = array_map(function ($height) {
            return ($height < 720 ? 'SD ' : 'HD ') . $height;
        }, $heights->toArray());

        $original = $this->files()->where('label', '=', PreviewFile::ORIGINAL)->first();

        $uploadable = $this->uploadable()->withTrashed()->first();

        if ($original && $uploadable && $uploadable->isProject()) {
            $user = $uploadable->user;

            if (!$user->plan->isFree() || $user->isAdmin()) {
                $heights[$original->id] = 'Original';
            }
        }

        return $heights;
    }

    public function getPreviewFilename()
    {
        $input = $this->preview_file_path;

        $filename = $this->preview_filename;

        // If no filename is supplied get the filename from the input
        if (!$filename) {
            $filename = pathinfo($input)['filename'];
        }

        return $filename;
    }

    /*
	|--------------------------------------------------------------------------
	| Repo Functions
	|--------------------------------------------------------------------------
	*/
    public function getPlaceholder($quality = 'low', $customRatio = false)
    {
        $previewFileRepository = app()->make('MotionArray\Repositories\PreviewFileRepository');

        $previewFile = null;

        if ($customRatio) {
            $previewFile = $previewFileRepository->findPlaceholder($this, $quality . '_custom');

            if ($quality == 'high' && isset($previewFile->label) && !str_contains($previewFile->label, '_custom')) {
                $previewFile = $previewFileRepository->findPlaceholder($this, 'low_custom');
            }

            if ($quality == 'high' && isset($previewFile->label) && !str_contains($previewFile->label, '_custom')) {
                $previewFile = $previewFileRepository->findPlaceholder($this, $quality);
            }
        }

        if (!$previewFile) {
            $previewFile = $previewFileRepository->findPlaceholder($this, $quality);
        }

        if ($previewFile) {
            return $previewFile->url;
        }
    }

    /**
     * Counts current uploads and sets a version for new one
     *
     * @return $this|null
     */
    function setVersion()
    {
        if ($this->uploadable) {
            $version = 1;

            $existingVersions = PreviewUpload::withTrashed()->where([
                'uploadable_id' => $this->uploadable_id,
                'uploadable_type' => $this->uploadable_type,
            ])->count();

            if ($existingVersions) {
                $version = $existingVersions;
            }

            $preview = $this->fresh();

            if (isset($preview->version)) {
                $preview->version = $version;

                $preview->save();
            }

            return $preview;
        }
    }

    function activateVersion()
    {
        $uploadable = $this->uploadable()->withTrashed()->first();

        if ($uploadable) {
            $uploadable->active_preview_id = $this->id;

            $uploadable->save();
        }
    }
}

PreviewUpload::created(function ($preview) {
    $preview->setVersion();

    $preview->activateVersion();
});

PreviewUpload::updated(function (PreviewUpload $previewUpload) {
    if ($previewUpload->placeholder_id != $previewUpload->getOriginal('placeholder_id')) {
        /** @var Uploadable|Product $product */
        $product = $previewUpload->uploadable()->first();

        if ($product instanceof Product && $product->isPublished()) {
            dispatch((new SendProductToAlgolia($product))->onQueue('high'));
        }
    }
});

PreviewUpload::deleted(function ($preview) {
    $uploadable = $preview->uploadable()->first();

    // If all the versions were removed, then delete the project
    if ($uploadable && $uploadable->isProject() && !$uploadable->previewUploads()->count()) {
        $uploadable->delete();
    }
});

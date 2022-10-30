<?php namespace MotionArray\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use MotionArray\Events\Encoder\EncondingDone;
use MotionArray\Events\Encoder\ReadyToEncode;
use MotionArray\Jobs\Product\GenerateProductImageMetaJob;
use MotionArray\Models\EventCode;
use MotionArray\Models\ModelRelease;
use MotionArray\Models\Product;
use MotionArray\Models\StaticData\EventCodes;
use MotionArray\Models\Traits\Uploadable;
use MotionArray\Models\Tag;
use Config;
use File;

abstract class UploadableRepository extends EloquentBaseRepository
{
    /**
     * Create or Update Preview for given product
     *
     * @param $uploadable
     * @param $attributes
     */
    public function upsertPreview(Uploadable $uploadable, $attributes)
    {
        $eventId = @$attributes['event_code_id'];

        $fields = ['fps', 'encoding_status_id', 'placeholder_id', 'preview_file_path', 'preview_filename', 'preview_extension'];

        $previewData = array_only($attributes, $fields);

        if (count($previewData)) {
            if (isset($previewData['encoding_status_id'])) {
                $previewData['encoding_status_id'] = (int)$previewData['encoding_status_id'];
            }

            $uploadable = $uploadable->fresh();

            $preview = $uploadable->activePreview;

            if ($preview && $eventId != 2) {
                $preview->fill($previewData)->save();
            } else {
                $uploadable->previewUploads()->create($previewData);
            }
        }
    }

    /**
     * Create or Update Preview for given product
     *
     * @param Product $product
     * @param array $attributes
     */
    public function saveModelRelease(Product $product, array $attributes)
    {
        ModelRelease::firstOrCreate(
            [
                'product_id' => $product->id,
                'url' => $attributes['model_release_url'],
                'filename' => $attributes['model_release_filename'],
            ]
        );
    }

    /**
     * @param $string
     *
     * @return array
     */
    public function processTags($string)
    {
        $saveTags = explode(",", strtolower($string));
        $storedTags = [];

        foreach ($saveTags as $tag) {
            $tag = trim($tag);

            $newTag = new Tag;
            $newTag->name = $tag;

            if ($newTag->save()) {
                $storedTags[] = $newTag;
            } else {
                $storedTags[] = Tag::where("name", "=", $tag)->first();
            }
        }

        return $storedTags;
    }

    /**
     * @param Uploadable|Product $uploadable
     */
    public function eventHandler($uploadable)
    {
        $uploadable = $uploadable->fresh();

        switch ($uploadable->event_code_id) {
            case EventCodes::READY_ID: // Ready
                break;
            case EventCodes::SEND_PREVIEW_FOR_ENCODING_ID:
                \syncEvent(ReadyToEncode::class, $uploadable);

                break;
            case EventCodes::STORE_VIDEO_PREVIEW_FILE_DETAILS_ID: // Store preview file details
                $output = $uploadable->activePreview->outputs()->where('label', '=', 'mp4 placeholders low')->first();

                if (!$output) {
                    $output = $uploadable->activePreview->outputs()->first();
                }

                \syncEvent(EncondingDone::class, $output);

                break;
            case EventCodes::DELETE_PREVIEW_ID: // Delete preview
                $this->deleteActivePreview($uploadable);

                if ($uploadable->isProduct()) {
                    $this->unpublish($uploadable);
                }

                break;
            case EventCodes::DELETE_PACKAGE_ID: // Delete package
                // Product only
                $this->deletePackage($uploadable);

                // if product is stock photo, we're removing preview for it.
                if ($uploadable->isImage()) {
                    $this->deleteActivePreview($uploadable);
                }

                break;
        }
    }

    /**
     * Get random audio placeholder
     * //TODO: Temporary method to set this placeholder
     */
    public function getRandomAudioPlaceholder()
    {
        $placeholders = [];

        foreach (File::allFiles(Config::get('info.audio_placeholders_path')) as $partial) {
            $placeholders[] = $partial->getFilename();
        }

        $i = rand(0, count($placeholders));

        if ($i >= 30) {
            $i--;
        }

        return Config::get('info.audio_placeholders_url') . $placeholders[$i];
    }

    /**
     * Deletes Uploadable and Versions
     *
     * @param Uploadable $uploadable
     * @param bool $includeFiles
     *
     * @throws \Exception
     */
    public function delete(Uploadable $uploadable, $includePreviews = true)
    {
        $previewUploadRepository = App::make('MotionArray\Repositories\PreviewUploadRepository');

        if ($includePreviews) {
            foreach ($uploadable->previewUploads()->get() as $previewUpload) {
                $previewUploadRepository->delete($previewUpload);
            }
        }

//        event(PreviewsWereDeleted::class, [$product]);

        $uploadable->delete();
    }

    /**
     * Delete products preview
     */
    public function deleteActivePreview(Uploadable $uploadable)
    {
        $previewUpload = $uploadable->activePreview;

        if ($previewUpload) {
            $previewUploadRepository = App::make('MotionArray\Repositories\PreviewUploadRepository');

            $previewUploadRepository->delete($previewUpload);
        }
    }
}

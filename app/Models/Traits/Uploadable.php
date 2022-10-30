<?php namespace MotionArray\Models\Traits;

use MotionArray\Models\BaseModel;
use MotionArray\Models\Output;
use URL;

abstract class Uploadable extends BaseModel
{
    protected $version;

    /*
	|--------------------------------------------------------------------------
	| Relationships & Scopes
	|--------------------------------------------------------------------------
	*/
    public function previewUploads()
    {
        return $this->morphMany('MotionArray\Models\PreviewUpload', 'uploadable')->orderBy('created_at');
    }

    public function activePreview()
    {
        return $this->hasOne('MotionArray\Models\PreviewUpload', 'id', 'active_preview_id');
    }

    public function activePreviewFiles()
    {
        $preview = $this->activePreview()->first();

        return $preview ? $preview->files()->get()->toArray() : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    /**
     * todo: remove
     *
     * Compatibility with old DB structure
     *
     * @return array
     */
    public function getPreviewsAttribute()
    {
        return $this->activePreviewFiles();
    }

    /**
     * todo: remove
     *
     * Compatibility with old DB structure
     *
     * @return mixed
     */
    public function getPlaceholderIdAttribute()
    {
        if ($this->activePreview) {
            return $this->activePreview->placeholder_id;
        }
    }

    /**
     * Generate an object containing S3 credentials.
     *
     * @return Array An array of preview and package AWS credentials
     */
    public function getAwsAttribute()
    {
        return [
            'preview' => $this->getAwsPreviewPolicy()
        ];
    }

    /*
	|--------------------------------------------------------------------------
	| Repo functions
	|--------------------------------------------------------------------------
	*/
    public function getPlaceholder()
    {
        $placeholder = null;

        if ($this->isVideo() || $this->isImage()) {
            $placeholder = $this->present()->getPreview('placeholder', 'high');
        } else {
            $placeholder = str_replace($this->previewsBucketUrl(), $this->cdnUrl(), $this->audio_placeholder);
        }

        return $placeholder;
    }

    public function getPlaceholderFallback()
    {
        $placeholder = $this->present()->getPreview('placeholder', 'high', null, true);

        return $placeholder;
    }

    public function getPlaceholderAbsoluteURL()
    {
        $placeholder = $this->getPlaceholder();

        if (!preg_match('#^http#', $placeholder)) {
            $placeholder = URL::to($placeholder);
        }

        return preg_replace('#\?(.*)#i', '', $placeholder);
    }

    /**
     * Is this product a video
     *
     * @return boolean
     */
    public function isVideo()
    {
        return $this->preview_type == "video";
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return $this->preview_type == "image";
    }

    /**
     * Is this product an audio
     *
     * @return boolean
     */
    public function isAudio()
    {
        return $this->preview_type == "audio";
    }

    /**
     * Is this a product
     *
     * @return bool
     */
    public function isProduct()
    {
        return $this->uploadType == 'product';
    }

    /**
     * Is this a Portfolio project / Review
     *
     * @return bool
     */
    public function isProject()
    {
        return $this->uploadType == 'project';
    }

    /**
     * @return array
     */
    public function getAwsPreviewPolicy()
    {
        $preview_type = $this->preview_type;
        $previewPolicy = $this->generateAWSPolicy($preview_type);

        return [
            'bucket' => $this->getBucket($preview_type),
            'key' => $this->getAWSKey(),
            'policy' => $previewPolicy,
            'signature' => $this->generateAWSSignature($previewPolicy),
            'bucketKey' => $this->getBucketKey($preview_type),
            'newFilename' => $this->generateFilename($this->previewPrefix . $this->id)
        ];
    }

    /**
     * Returns the upload with the position of the given number
     *
     * @param $version
     */
    public function getVersionNumber($version = 1)
    {
        if (!$version && $this->version) {
            $version = $this->version;
        }

        return $this->previewUploads()->where('version', '=', $version)->first();
    }

    public function getOutputs()
    {
        $uploadIds = $this->previewUploads()->pluck('id');

        return Output::whereIn('preview_upload_id', $uploadIds)->get();
    }
}

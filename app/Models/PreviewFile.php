<?php namespace MotionArray\Models;

use AWS;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreviewFile extends BaseModel
{
    use SoftDeletes;

    const ORIGINAL = 'ORIGINAL';
    const MP3_HIGH = 'mp3 high';
    const MP4_HIGH = 'mp4 high';

    protected $guarded = [];

    public static $rules = [];

    /*
	|--------------------------------------------------------------------------
	| Scopes
	|--------------------------------------------------------------------------
	*/
    public function scopeVideo($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('format', ['mpeg4', 'webm', 'ogg', 'mpeg-ts'])
                //->orWhere('label','hls playlist')
                ->orWhere('label', '=', 'ORIGINAL');
        })
            ->orderByRaw('FIELD(format,"mpeg-ts","mpeg4","webm","ogg")');
    }

    public function scopeThumbnail($query)
    {
        return $query->where('label', 'LIKE', 'placeholder%');
    }

    public function scopeOriginal($query)
    {
        return $query->where('label', '=', static::ORIGINAL);
    }

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function previewUpload()
    {
        return $this->belongsTo('MotionArray\Models\PreviewUpload');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors and Mutators
    |--------------------------------------------------------------------------
    */
    public function getSizeAttribute($value)
    {
        return $this->file_size_bytes / 1073741824;
    }

    public function getQualityAttribute($value)
    {
        preg_match('(high|mid|low)', $this->label, $matches);

        return $matches[0];
    }

    /*
	|--------------------------------------------------------------------------
	| Repo Functions
	|--------------------------------------------------------------------------
	*/

    public function isOriginal()
    {
        return $this->label == static::ORIGINAL;
    }

    /**
     * Generates a signed download URL for the preview.
     */
    public function getDownloadUrl()
    {
        $previewUpload = $this->previewUpload;

        $uploadable = $previewUpload->uploadable()->first();

        if (!$uploadable) {
            return;
        }

        $bucket = $uploadable->previewsBucket();

        $exploded_url = explode('/', $this->url);

        $key = array_pop($exploded_url);

        $exploded_url = explode('.', $this->url);

        $extension = array_pop($exploded_url);

        $type = 'Preview';

        if ($this->isOriginal()) {
            $type = 'Original';
        }

        $filename = $uploadable->slug . '-' . strtoupper($type) . '.' . $extension;

        $s3 = AWS::get('s3');

        return $s3->getObjectUrl($bucket, $key, '+5 minutes', [
            'ResponseContentDisposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}

PreviewFile::saving(function ($file) {
    if (!$file->format) {
        $ext = pathinfo($file->url, PATHINFO_EXTENSION);

        $file->format = $ext;
    }

    if ($file->format == 'mp4') {
        $file->format = 'mpeg4';
    }
});

PreviewFile::created(function ($previewFile) {
    if ($previewFile->isOriginal()) {
        $explode = explode('original-', $previewFile->url);

        if (isset($explode[1])) {
            $key = 'staging/preview-' . $explode[1];

            $userUpload = \App::make('MotionArray\Repositories\UserUploadRepository');

            $userUpload->deleteUploadingRecord($key);
        }
    }
});

<?php namespace MotionArray\Models;

use AWS;

class PortfolioUpload extends BaseModel
{
    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function portfolio()
    {
        return $this->belongsTo('\MotionArray\Models\Portfolio');
    }

    /**
     * Generates a signed download URL for the preview.
     */
    public function getDownloadUrl()
    {
        $s3 = AWS::get('s3');

        $bucket = config('aws.portfolio_previews_bucket');

        $exploded_url = parse_url($this->url);

        $key = $exploded_url['path'];

        $filename = basename(str_replace(' ', '', $key));

        $key = preg_replace('#^\/#', '', $key);

        $url = $s3->getObjectUrl($bucket, $key, '+5 minutes', [
            'ResponseContentDisposition' => 'attachment; filename="' . $filename . '"'
        ]);

        return $url;
    }
}
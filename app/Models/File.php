<?php namespace MotionArray\Models;

use AWS;
use Config;

class File extends BaseModel
{
    /**
     * Save the model to the database.
     * @param  array $options
     * @return bool
     */
    public function saveBase64(array $options = [])
    {
        $s3 = AWS::get('s3');
        $imageBase64 = explode(',', $this->base64);
        $image = base64_decode($imageBase64[1]);
        $response = $s3->putObject([
            'Bucket' => Config::get('aws.files_bucket'),
            'Body' => $image,
            'Key' => $this->filename,
            'ACL' => 'public-read',
            'ContentEncoding' => 'base64',
            'ContentType' => $this->mime_type,
            'CacheControl' => 'public, max-age=31104000'
        ]);
        $this->url = $response['ObjectURL'];
        unset($this->base64);
        unset($this->filename);

        return parent::save($options);
    }
}

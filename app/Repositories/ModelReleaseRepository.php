<?php namespace MotionArray\Repositories;

use AWS;
use Config;
use MotionArray\Models\ModelRelease;

class ModelReleaseRepository extends EloquentBaseRepository
{
    /**
     * @param ModelRelease $modelRelease
     * @return string
     */
    public function getDownloadUrl(ModelRelease $modelRelease)
    {
        /** @var S3Client $s3 */
        $s3 = AWS::get('s3');

        $bucket = config("aws.model_releases_bucket");
        $url = $s3->getObjectUrl($bucket, $modelRelease->filename, config('aws.packages_signed_url_expiration'));

        return $url;
    }

    /**
     * @param ModelRelease $modelRelease
     * @return boolean
     * @throws \Exception
     */
    public function delete(ModelRelease $modelRelease): bool
    {
        /** @var S3Client $s3 */
        $s3 = AWS::get('s3');

        // Documentation: https://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.S3.S3Client.html#_deleteObject
        $s3->deleteObject([
            'Bucket' =>  Config::get("aws.model_releases_bucket"),
            'Key' => $modelRelease->filename
        ]);

        return $modelRelease->delete();
    }
}

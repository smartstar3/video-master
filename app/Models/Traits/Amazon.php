<?php namespace MotionArray\Models\Traits;

use Config;
use AWS;
use MotionArray\Models\PreviewFile;
use MotionArray\Models\PreviewUpload;

// Uploadable
Trait Amazon
{
    public static function previewsBucketUrl()
    {
        return 'http://' . self::previewsBucket() . '.s3.amazonaws.com/';
    }

    public function getBucketKey($file_type)
    {
        if ($file_type == 'video') return Config::get('aws.previews_key');
        if ($file_type == 'audio') return Config::get('aws.previews_audio_key');
        if ($file_type == 'image') return Config::get('aws.previews_image_key');
        if ($file_type == 'package') return Config::get('aws.packages_key');
    }

    public function getBucket($bucket)
    {
        if ($bucket == 'video') return $this->previewsBucket();
        if ($bucket == 'audio') return $this->previewsBucket();
        if ($bucket == 'image') return $this->previewsBucket();
        if ($bucket == 'package') return Config::get('aws.packages_bucket');
        if ($bucket == 'model_release') return Config::get('aws.model_releases_bucket');
    }

    public function generateAWSPolicy($bucket, $expire_in = 86400)
    {
        if ($bucket == 'video') {
            $bucket = $this->previewsBucket();
            $key = Config::get('aws.previews_key');
        }

        if ($bucket == 'audio') {
            $bucket = $this->previewsBucket();
            $key = Config::get('aws.previews_audio_key');
        }

        if ($bucket == 'package') {
            $bucket = Config::get('aws.packages_bucket');
            $key = Config::get('aws.packages_key');
        }

        if ($bucket == 'image') {
            $bucket = $this->previewsBucket();
            $key = Config::get('aws.previews_image_key');
        }

        if ($bucket == 'model_release') {
            $bucket = Config::get('aws.model_releases_bucket');
            $key = Config::get('aws.model_releases_key');
        }

        $expiration = date('Y-m-d\TH:i:s\Z', time() + $expire_in);

        $policy = '{
            "expiration": "' . $expiration . '",
            "conditions": [
                {
                    "bucket": "' . $bucket . '"
                },
                {
                    "acl": ""
                },
                [
                    "starts-with",
                    "$key",
                    "' . $key . '"
                ],
                {
                    "success_action_status": "201"
                }
            ]
        }';

        return base64_encode($policy);
    }

    public function generateAWSSignature($policy)
    {
        return base64_encode(hash_hmac("sha1", $policy, Config::get('aws.secret'), true));
    }

    public function getAWSKey()
    {
        return Config::get('aws.key');
    }

    public function getPreviewDownloadUrl($format)
    {
        if ($this->preview_filename && $this->preview_extension) {
            // Staged File
            $bucket = $this->previewsBucket();

            $key = $this->preview_filename . "." . $this->preview_extension;

            $filename = str_replace(' ', '', $this->name) . "-Preview." . $this->preview_extension;

            $s3 = AWS::get('s3');

            return $s3->getObjectUrl($bucket, $key, '+5 minutes', [
                'ResponseContentDisposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } else {
            /** @var PreviewUpload $preview */
            $preview = $this->activePreview;

            if ($preview) {
                /** @var PreviewFile $file */
                $file = $preview->files()->where('format', '=', $format)->first();

                if ($file) {
                    return $file->getDownloadUrl();
                }
            }
        }

        return false;
    }

    public function getAWSFileKey()
    {
        $url = $this->package_file_path;

        $key = basename($url);

        return $key;
    }

    public function generateFilename($prefix = '', $extension = '')
    {
        $filename = str_random(10);
        if ($prefix) $filename = $prefix . '-' . $filename;
        if ($extension) $filename = $filename . "." . $extension;

        return $filename;
    }
}

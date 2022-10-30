<?php namespace MotionArray\Helpers;

use Illuminate\Support\Facades\Config;
use MotionArray\Models\Product;
use MotionArray\Models\Project;

class Imgix
{
    protected static $q = 60; // Default Quality

    /**
     * todo: Send uploadable to identify bucket
     *
     * @param $url
     * @param $width
     *
     * @return mixed|string
     */
    public static function getImgixUrl($url, $width = null, $height = null, $crop = null)
    {
        $useImgix = Config::get('imgix.use_imgix');

        if (!$useImgix) {
            return $url;
        }

        $configArr = [
            [
                'bucket' => Product::previewsBucket(),
                'bucketUrl' => Product::bucketUrl(),
                'cdnUrl' => Product::cdnUrl(),
                'imgixUrl' => Product::imgixUrl(),
            ], [
                'bucket' => Project::previewsBucket(),
                'bucketUrl' => Project::bucketUrl(),
                'cdnUrl' => Project::cdnUrl(),
                'imgixUrl' => Project::imgixUrl(),
            ], [
                'bucket' => 'ma-content',
                'bucketUrl' => 'http://ma-content.s3.amazonaws.com/',
                'cdnUrl' => null,
                'imgixUrl' => 'https://motionarray-content.imgix.net/',
            ]
        ];

        foreach ($configArr as $config) {

            // Dev fix
            $bucket = str_replace('-dev', '', $config['bucket']);
            $bucket .= '(-dev)?';

            $find = '#https?:\/\/' . $bucket . '(.*)\.s3\.amazonaws.com\/#i';

            if (preg_match('/jpeg/', $url) || preg_match('/jpg/', $url) || preg_match('/png/', $url)) {
                $replace = $config['imgixUrl'];

                $replacedUrl = preg_replace($find, $replace, $url);

                $queryParams = [];

                if ($replacedUrl != $url) {
                    if ($width) {
                        $query["w"] = $width;
                    }

                    if ($height) {
                        $query["h"] = $height;
                    }

                    if ($crop) {
                        $query["rect"] = $crop;
                    }

                    $query["q"] = $width == 'auto' ? '100' : self::$q;
                    $query["fit"] = 'max';
                    $query["auto"] = 'format';

                    $url = $replacedUrl . '?' . http_build_query($query);

                    break;
                }
            } else {
                $url = str_replace($config['bucketUrl'], $config['cdnUrl'], $url);
            }
        }

        return $url;
    }
}
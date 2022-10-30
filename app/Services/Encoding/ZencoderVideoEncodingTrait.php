<?php namespace MotionArray\Services\Encoding;

use MotionArray\Models\Traits\Uploadable;

Trait ZencoderVideoEncodingTrait
{
    public function getWatermark()
    {
        return [
            "url" => "https://s3.amazonaws.com/ma-previews/staging/watermark.png",
            "x" => 0,
            "y" => 0,
            "width" => "100%",
            "height" => "100%"
        ];
    }

    /**
     * Send encoding create request to Zencoder
     *
     * @param  string $input
     * @param  string $filename
     *
     * @return object           Results or Exception
     */
    public function encodeVideo(Uploadable $uploadable, $addWatermark = true)
    {
        $filename = $uploadable->activePreview->getPreviewFilename();

        $lowResolutions = [
            'low' => '640x360'
        ];
        $highResolutions = [
            'high' => '1280x720',
        ];

        if ($uploadable->isProject()) {
            $lowResolutions = [
                'low' => '852x480',
//                'low_custom' => '852x852'
            ];
            $highResolutions = [
                'high' => '1920x1080',
//                'high_custom' => '1920x1920',
            ];
        }

        $resolutions = array_merge($lowResolutions, $highResolutions);

        $hls = $uploadable->isProject();

        $optionsLowQuality = $this->encodeVideoThumbnails($uploadable, $lowResolutions, $filename);
        $optionsHighQuality = $this->encodeVideoThumbnails($uploadable, $highResolutions, $filename);
        $mp4Encoding = $this->encodeVideoPreviews($uploadable, $resolutions, $filename, ['mp4'], $addWatermark, $hls);
        $webmEncoding = $this->encodeVideoPreviews($uploadable, $resolutions, $filename, ['webm'], $addWatermark);

        if ($uploadable->isProject()) {
            $this->createEncodingJob($uploadable, $optionsLowQuality);
            $this->createEncodingJob($uploadable, $optionsHighQuality, true);
            $this->createEncodingJob($uploadable, $mp4Encoding, true);
            $this->createEncodingJob($uploadable, $webmEncoding, true);
        } else {
            $options = array_merge($optionsLowQuality, $optionsHighQuality, $mp4Encoding, $webmEncoding);

            $this->createEncodingJob($uploadable, $options);
        }
    }

    /**
     * @param Uploadable $uploadable
     * @param $resolutions
     * @param $filename
     */
    public function encodeVideoThumbnails(Uploadable $uploadable, $resolutions, $filename)
    {
        $options = [];
        $bucket = 's3://' . $uploadable->previewsBucket() . '/';

        foreach ($resolutions as $res => $resolution) {
            $option = [
                "label" => "mp4 placeholders " . $res,
                "h264_profile" => "high",
                "format" => "mp4",
                "size" => $resolution,
                "public" => 1,
                "headers" => [
                    "Cache-Control" => $this->cacheControl,
                    "Expires" => $this->expires
                ],
                "thumbnails" => [
                    "label" => "placeholder " . $res,
                    "number" => 16,
                    "base_url" => $bucket,
                    "format" => "jpg",
                    "size" => $resolution,
                    "prefix" => $filename . '-' . $res,
                    "public" => 1,
                    "headers" => [
                        "Cache-Control" => $this->cacheControl,
                        "Expires" => $this->expires
                    ],
                ],
                "url" => $bucket . $filename . '-placeholders-' . $res . '.mp4'
            ];

            // If resolution label doesnt contains "custom"
            if (strpos($res, 'custom') === false) {
                $option["aspect_mode"] = "crop";
            }

            $options[] = $option;
        }

        return $options;
    }

    /**
     * @param Uploadable $uploadable
     * @param $resolutions
     * @param $filename
     * @param bool $addWatermark
     */
    public function encodeVideoPreviews(Uploadable $uploadable, $resolutions, $filename, $formats, $addWatermark = false, $hls = false)
    {
        $options = [];
        $bucket = 's3://' . $uploadable->previewsBucket() . '/';

        foreach ($resolutions as $res => $resolution) {
            foreach ($formats as $format) {
                $option = [
                    "label" => $format . " " . $res,
                    "format" => $format,
                    "size" => $resolution,
                    "public" => 1,
                    "headers" => [
                        "Cache-Control" => $this->cacheControl,
                        "Expires" => $this->expires
                    ],
                    "url" => $bucket . $filename . '-' . $res . '.' . $format
                ];

                // If resolution label doesnt contains "custom"
                if (strpos($res, 'custom') === false) {
                    $option["aspect_mode"] = "crop";
                }

                if ($format == 'mp4' && $res == 'high') {
                    $option["h264_profile"] = "high";
                }

                // Add watermark
                if ($addWatermark) {
                    $option["watermark"] = $this->getWatermark();
                }

                if ($uploadable->isProject()) {
                    $option["quality"] = 4;
                }

                $options[] = $option;
            }
        }

        if ($hls && in_array('mp4', $formats)) {
            $hlsOptions = $this->getHlsEncodingSettings($resolutions, $filename, $bucket);

            $options = array_merge($options, $hlsOptions);
        }

        return $options;
    }

    /**
     * @param $resolutions
     * @param $filename
     * @param $bucket
     *
     * @return array
     */
    public function getHlsEncodingSettings($resolutions, $filename, $bucket)
    {
        $hlsOptions = [];

        foreach ($resolutions as $res => $resolution) {
            $hlsFilename = $filename . "-" . $res . ".m3u8";

            $hlsOptions[] = [
                "source" => "mp4 " . $res,
                "format" => "ts",
                "copy_audio" => "true",
                "copy_video" => "true",
                "url" => $bucket . $hlsFilename,
                "label" => "hls " . $res,
                "type" => "segmented",
                "quality" => 4,

                "public" => 1,
                "headers" => [
                    "Cache-Control" => $this->cacheControl,
                    "Expires" => $this->expires
                ],
            ];
        }

        return $hlsOptions;
    }
}

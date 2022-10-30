<?php namespace MotionArray\Services\Encoding;

use MotionArray\Models\Traits\Uploadable;

Trait ZencoderAudioEncodingTrait
{

    /**
     * @param $input
     * @param string $filename
     *
     * @return \Exception|Services_Zencoder_Exception|\Services_Zencoder_Job
     * @throws \Services_Zencoder_Exception
     */
    public function encodeAudio(Uploadable $uploadable)
    {
        $filename = $uploadable->activePreview->getPreviewFilename();

        $encodingOutputSettings = $this->getAudioEncodingOutputSettings($filename);

        // Send create request to Zencoder or throw exception
        $this->createEncodingJob($uploadable, $encodingOutputSettings);
    }

    /**
     * @return array
     */
    protected function getAudioEncodingOutputSettings($filename)
    {
        $formats = ['mp3', 'ogg'];
        $encodingOutput = [];

        $zencoderBucket = config('zencoder.bucket');

        // For each output
        foreach ($formats as $format) {
            $encodingOutput[] = [
                "label" => $format . " high",
                "format" => $format,
                "public" => 1,
                "headers" => [
                    "Cache-Control" => $this->cacheControl,
                    "Expires" => $this->expires
                ],
                "url" => $zencoderBucket . $filename . "." . $format
            ];
        }

        return $encodingOutput;
    }
}

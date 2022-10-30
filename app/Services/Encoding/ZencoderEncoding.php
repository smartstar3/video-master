<?php namespace MotionArray\Services\Encoding;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use MotionArray\Models\Output;
use MotionArray\Models\Traits\Uploadable;
use Services_Zencoder_Exception;
use Exception;

class ZencoderEncoding implements EncodingInterface
{
    use ZencoderVideoEncodingTrait, ZencoderAudioEncodingTrait;

    protected $cacheControl;

    protected $expires;

    protected $zencoder;

    public function __construct()
    {
        $this->cacheControl = "public, max-age=31104000"; // 360 Days

        $this->expires = date(DATE_RFC2822, strtotime("+360 days"));

        $key = Config::get('zencoder.key');

        $this->zencoder = new \Services_Zencoder($key, 'v2', 'https://app.zencoder.com/');
    }

    /**
     * Encode Uploadable
     *
     * @param $uploadable
     * @param $add_watermark
     *
     * @return \Exception|Services_Zencoder_Exception|object|\Services_Zencoder_Job
     */
    public function encode(Uploadable $uploadable, $addWatermark = false)
    {
        if ($uploadable->isAudio()) {
            return $this->encodeAudio($uploadable);
        } else {
            return $this->encodeVideo($uploadable, $addWatermark);
        }
    }

    /**
     * @param Uploadable $uploadable
     * @param $encodingConfig
     *
     * @throws Services_Zencoder_Exception
     */
    public function createEncodingJob(Uploadable $uploadable, $encodingOutputSettings, $notify = false)
    {
        $previewUpload = $uploadable->activePreview;

        $input = $previewUpload->preview_file_path;

        $this->createEncodingJobFromUrl($uploadable, $input, $encodingOutputSettings, $notify);
    }

    /**
     * @param Uploadable $uploadable
     * @param $input
     * @param $encodingOutputSettings
     * @param bool $notify
     *
     * @return Exception|Services_Zencoder_Exception
     */
    public function createEncodingJobFromUrl(Uploadable $uploadable, $input, $encodingOutputSettings, $notify = false)
    {
        $encodingSettings = [
            'input' => $input,
            'outputs' => $encodingOutputSettings
        ];

        if ($notify && !App::isLocal()) {
            $encodingSettings['notifications'] = [
                URL::to('/zencoder/webhook/')
            ];
        }

        return $this->createEncodeJobFromSettings($uploadable, $encodingSettings);
    }

    /**
     * Get output progress JSON
     *
     * @param integer $id
     *
     * @return json
     */
    public function getOutputProgress($id)
    {
        return $this->zencoder->retrieveData('outputs/' . $id . '/progress.json');
    }

    /**
     * Get output details JSON
     *
     * @param integer $id
     *
     * @return json
     */
    public function getOutputDetails($id)
    {
        return $this->zencoder->retrieveData('outputs/' . $id . '.json');
    }

    /**
     * Get job progress JSON
     *
     * @param integer $id
     *
     * @return json
     */
    public function getJobProgress($id)
    {
        return $this->zencoder->retrieveData('jobs/' . $id . '/progress.json');
    }

    /**
     * Get job details JSON
     *
     * @param integer $id
     *
     * @return json
     */
    public function getJobDetails($id)
    {
        return $this->zencoder->retrieveData('jobs/' . $id . '.json');
    }

    /**
     * Cancel job
     *
     * @param integer $id
     *
     * @return json
     */
    public function cancelJob($id)
    {
        try {
            return $this->zencoder->jobs->cancel($id);
//            $response = $this->client->put("jobs/$id/cancel.json?api_key=$this->zencoderKey")->send();
//            return $response->getStatusCode();
        } catch (Exception $e) {
            return 409;
        }
    }

    /**
     * @param Uploadable $uploadable
     * @param $encodingSettings
     * @return Exception|Services_Zencoder_Exception
     */
    public function createEncodeJobFromSettings(Uploadable $uploadable, $encodingSettings)
    {
        $previewUpload = $uploadable->activePreview;

        $encodingJob = null;

        try {
            $encodingJob = $this->zencoder->jobs->create($encodingSettings);
        } catch (Services_Zencoder_Exception $e) {
            $type = $uploadable->isProduct() ? 'product' : 'project';

            $errorMessage = 'Error encoding ' . $type . ' ' . $uploadable->id . ' ' . $e->getMessage();

            \Log::error($errorMessage);

            return $e;
        }

        // Save outputs for encoding files
        if ($encodingJob) {
            foreach ($encodingJob->outputs as $jobOutput) {
                $output = new Output();
                $output->job_id = $encodingJob->id;
                $output->label = $jobOutput->label;
                $output->url = $jobOutput->url;
                $output->output_id = $jobOutput->id;

                $previewUpload->outputs()->save($output);
            }
        }
    }
}

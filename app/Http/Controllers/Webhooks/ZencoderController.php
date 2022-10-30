<?php

namespace MotionArray\Http\Controllers\Webhooks;

use MotionArray\Http\Controllers\Site\BaseController;
use MotionArray\Models\Output;
use MotionArray\Repositories\PreviewUploadRepository;
use Response;
use Request;

class ZencoderController extends BaseController
{
    protected $previewUpload;

    public function __construct(PreviewUploadRepository $previewUpload)
    {
        $this->previewUpload = $previewUpload;
    }

    public function webhook()
    {
        $notification = Request::all();

        $jobId = $notification['job']['id'];

        $output = Output::where('job_id', '=', $jobId)->first();

        if ($output && $output->id) {
            $notify = Output::where('job_id', '=', $jobId)->where('label', '=', 'mp4 high')->exists();

            $uploadable = null;
            if ($output) {
                $previewUpload = $output->previewUpload;

                // Send success as response if preview upload does not exist,
                // so that zencoder does not retry the notification.
                if (!$previewUpload) {
                    return Response::json([
                        'success' => true,
                        'output' => $output
                    ]);
                }

                $uploadable = $previewUpload->uploadable;
            }

            if ($uploadable && $uploadable->isProduct()) {
                $notify = false;
            }

            $this->previewUpload->storeJobPreviews($output, $notify);

            return Response::json([
                'success' => true,
                'output' => $output
            ]);
        }

        return Response::json([
            'success' => false
        ], 404);
    }
}

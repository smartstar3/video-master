<?php

namespace MotionArray\Listeners\Encoder;

use MotionArray\Models\DebugLog;
use MotionArray\Models\Output;
use MotionArray\Models\Traits\Uploadable;
use MotionArray\Repositories\PreviewUploadRepository;

class StorePreviews
{
    protected $producerMailer;

    /**
     * Create the event listener.
     *
     */
    public function __construct(PreviewUploadRepository $previewUpload)
    {
        $this->previewUpload = $previewUpload;
    }

    /**
     * Handle the event.
     *
     * @param Uploadable $uploadable
     * @param bool $notify
     */
    public function handle(Output $output = null, $notify = false)
    {
        if (!$output) {
            return;
        }

        $this->previewUpload->storeJobPreviews($output, $notify);
    }
}

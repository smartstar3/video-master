<?php

namespace MotionArray\Listeners\Encoder;

use MotionArray\Models\PreviewUpload;
use MotionArray\Models\Product;
use MotionArray\Repositories\PreviewUploadRepository;

class CleanUp
{
    private $previewUpload;

    /**
     * Create the event listener.
     *
     * @param PreviewUpload $previewUpload
     */
    public function __construct(PreviewUploadRepository $previewUpload)
    {
        $this->previewUpload = $previewUpload;
    }

    /**
     * Handle the event.
     *
     * @param Product $product
     */
    public function handle(PreviewUpload $previewUpload, $cancelledJob = false, $jobDetails)
    {
        if ($cancelledJob) {
            $this->previewUpload->deleteS3Files($previewUpload);
        } else {
            $this->previewUpload->cleanUp($previewUpload, $jobDetails);
        }
    }
}

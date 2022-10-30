<?php

namespace MotionArray\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MotionArray\Models\PreviewUpload;
use MotionArray\Repositories\PreviewUploadRepository;
use MotionArray\Services\MediaSender\HttpMediaSender;

class DeleteS3Files extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $previewUpload;

    /**
     * Create a new job instance.
     *
     * @param PreviewUpload $previewUpload
     */
    public function __construct(PreviewUpload $previewUpload)
    {
        $this->previewUpload = $previewUpload;
    }

    /**
     * @param HttpMediaSender $mediaSender
     */
    public function handle(PreviewUploadRepository $previewUploadRepo)
    {
        $previewUploadRepo->deleteS3Files($this->previewUpload);
    }
}

<?php

namespace MotionArray\Listeners;

use Google_Service_YouTube;
use MotionArray\Models\Product;

class DeletePreviewFromYouTube
{
    /**
     * @var Google_Service_YouTube
     */
    private $youtube;

    /**
     * Create the event listener.
     *
     * @param Google_Service_YouTube $youtube
     */
    public function __construct(Google_Service_YouTube $youtube)
    {
        $this->youtube = $youtube;
    }

    /**
     * Handle the event.
     *
     * @param Product $product
     */
    public function handle(Product $product)
    {
        $previewUpload = $product->activePreview;

        if (!$previewUpload || !$previewUpload->youtube_id) {
            return;
        }

        if ($this->youtube->videos->listVideos('snippet', ['id' => $previewUpload->youtube_id])->count()) {
            $this->youtube->videos->delete($previewUpload->youtube_id);
        }

        $previewUpload->update(['youtube_id' => null]);
    }
}

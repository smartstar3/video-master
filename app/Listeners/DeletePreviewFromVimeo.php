<?php

namespace MotionArray\Listeners;

use MotionArray\Models\Product;
use Vimeo\Vimeo;

class DeletePreviewFromVimeo
{
    /**
     * @var Vimeo
     */
    private $vimeo;

    /**
     * Create the event listener.
     *
     * @param Vimeo $vimeo
     */
    public function __construct(Vimeo $vimeo)
    {
        $this->vimeo = $vimeo;
    }

    /**
     * Handle the event.
     *
     * @param Product $product
     */
    public function handle(Product $product)
    {
        $previewUpload = $product->activePreview;

        if (!$previewUpload || !$previewUpload->vimeo_id) {
            return;
        }

        $this->vimeo->request('/videos/' . $previewUpload->vimeo_id, [], 'DELETE');

        $previewUpload->update(['vimeo_id' => null]);
    }
}

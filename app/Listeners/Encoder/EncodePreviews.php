<?php

namespace MotionArray\Listeners\Encoder;

use MotionArray\Models\DebugLog;
use MotionArray\Models\Output;
use MotionArray\Models\Product;
use MotionArray\Models\Traits\Uploadable;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Repositories\ProjectRepository;
use MotionArray\Services\Encoding\EncodingInterface;

class EncodePreviews
{
    private $encoder;

    private $product;

    private $project;

    /**
     * Create the event listener.
     *
     * @param ProductRepository $product
     * @param ProjectRepository $project
     */
    public function __construct(EncodingInterface $encoder, ProductRepository $product, ProjectRepository $project)
    {
        $this->encoder = $encoder;

        $this->product = $product;

        $this->project = $project;
    }

    /**
     * Handle the event.
     *
     * @param Product $product
     */
    public function handle(Uploadable $uploadable)
    {
        $add_watermark = false;

        if ($uploadable->isProduct()) {
            $repository = $this->product;

            $add_watermark = $uploadable->category->add_watermark;
        } else {
            $repository = $this->project;
        }

        $this->encoder->encode($uploadable, $add_watermark);

        $repository->update($uploadable->id, [
            'encoding_status_id' => 2, // In progress
            'event_code_id' => 1  // Ready
        ]);
    }
}

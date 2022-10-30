<?php

namespace MotionArray\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MotionArray\Models\Product;
use MotionArray\Services\MediaSender\HttpMediaSender;

class SendPreviews extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $product;

    /**
     * Create a new job instance.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @param HttpMediaSender $mediaSender
     */
    public function handle(HttpMediaSender $mediaSender)
    {
        $mediaSender->send($this->product);
    }
}

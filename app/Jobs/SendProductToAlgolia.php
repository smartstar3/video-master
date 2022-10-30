<?php

namespace MotionArray\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MotionArray\Models\Product;
use MotionArray\Repositories\Products\ProductRepository;

class SendProductToAlgolia extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function handle(ProductRepository $productRepository)
    {
        $productRepository->updateAlgoliaDataForProduct($this->product->id);
    }
}

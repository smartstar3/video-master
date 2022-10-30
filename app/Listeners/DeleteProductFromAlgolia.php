<?php namespace MotionArray\Listeners;

use MotionArray\Models\Product;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Services\Algolia\AlgoliaClient;

class DeleteProductFromAlgolia
{
    /**
     * @var ProductRepository
     */
    private $algolia;

    /**
     * Create the event listener.
     *
     * @param ProductRepository $product
     */
    public function __construct(AlgoliaClient $algolia)
    {
        $this->algolia = $algolia;
    }

    /**
     * Handle the event.
     *
     * @param Product $product
     */
    public function handle(Product $product)
    {
        $this->algolia->removeProduct($product->id);
    }
}

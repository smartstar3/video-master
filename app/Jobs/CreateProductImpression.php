<?php

namespace MotionArray\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use MotionArray\Models\Product;
use MotionArray\Models\User;
use MotionArray\Repositories\ProductImpressionRepository;

/**
 * Class CreateProductImpression
 *
 * @package MotionArray\Jobs
 */
class CreateProductImpression extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Product Object
     *
     * @var Product
     */
    private $product;

    /**
     * User Object
     *
     * @var User
     */
    private $user;

    /**
     * CreateProductImpression constructor.
     *
     * @param Product $product
     * @param User $user
     */
    public function __construct(Product $product, User $user)
    {
        $this->product = $product;
        $this->user = $user;
    }

    /**
     * Job Handle
     *
     * @param \MotionArray\Repositories\ProductImpressionRepository $productImpression
     */
    public function handle(ProductImpressionRepository $productImpression)
    {
        $productImpression->create($this->product, $this->user);
    }
}

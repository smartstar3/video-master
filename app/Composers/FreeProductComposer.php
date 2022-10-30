<?php namespace MotionArray\Composers;

use MotionArray\Repositories\Products\ProductRepository;
use Flash;
use Request;

class FreeProductComposer
{

    protected $product;

    public function __construct(ProductRepository $product)
    {
        $this->product = $product;
    }

    public function compose($view)
    {
        $product_id = Request::get("product");

        $product = null;

        if ($product_id) {
            $product = $this->product->findById($product_id);

            if ($product) {
                $product = $product->free ? $product : null;
            }
        }

        $view->with(compact("product"));
    }

}
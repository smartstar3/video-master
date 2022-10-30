<?php namespace MotionArray\Helpers;

use App;

class ReplaceContent
{
    public static function productsCount($content)
    {
        $productRepository = App::make('MotionArray\Repositories\Products\ProductRepository');

        $find = ['[product_count]', '[product_count_period]', '[new_period]'];

        $replace = [
            $productRepository->getProductsCount(),
            $productRepository->getProductsCountCreatedInLast(30),
            30
        ];

        return str_replace($find, $replace, $content);
    }
}
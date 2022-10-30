<?php namespace MotionArray\Console\Commands\OneTime;


use Illuminate\Console\Command;
use MotionArray\Models\Product;


class FixFreeProductSubCategories extends Command
{
    protected $name = 'motionarray-onetime:fix-free-product-sub-categories';

    protected $description = 'Fix free product subcategories';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->fixPayedProducts();

        $this->fixFreeProducts();
    }

    /**
     * Remove free subcategory from products that are not free
     */
    protected function fixPayedProducts()
    {
        $notFreeProducts = Product::where('free', '=', false)->whereHas('subCategories', function ($query) {
            $query->where('slug', '=', 'free');
        })->get();

        $this->info('Processing ' . count($notFreeProducts) . ' not free products');

        foreach ($notFreeProducts as $product) {
            $freeSubCategory = $product->subCategories()->where('slug', '=', 'free')->first();

            $product->subCategories()->detach($freeSubCategory->id);
        }
    }

    /**
     * Add free subcategory to all free products
     */
    protected function fixFreeProducts()
    {
        $freeProducts = Product::where('free', '=', true)->whereDoesntHave('subCategories', function ($query) {
            $query->where('slug', '=', 'free');
        })->get();

        $this->info('Processing ' . count($freeProducts) . ' free products');

        foreach ($freeProducts as $product) {
            $freeSubCategory = $product->category->subCategories()->where('slug', '=', 'free')->first();

            if ($freeSubCategory) {

                $product->subCategories()->save($freeSubCategory);
            }
        }
    }
}
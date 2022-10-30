<?php namespace MotionArray\Composers;

use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Models\StaticData\Helpers\CategoryGroupsWithRelationsHelper;

class GrowthComposer
{

    protected $product;

    /**
     * @var CategoryGroupsWithRelationsHelper
     */
    protected $categoryGroupsWithRelationsHelper;

    public function __construct(
        ProductRepository $product,
        CategoryGroupsWithRelationsHelper $categoryGroupsWithRelationsHelper
    )
    {
        $this->product = $product;
        $this->categoryGroupsWithRelationsHelper = $categoryGroupsWithRelationsHelper;
    }

    public function compose($view)
    {
        $stats = [
            'product_count' => $this->product->getProductsCount(),
            'new_products' => $this->product->getProductsCountCreatedInLast(30),
            'new_period' => 30
        ];
        $categoryGroups = $this->categoryGroupsWithRelationsHelper->categoryGroupsWithCategories(null, true);

        $view->with(compact('stats', 'categoryGroups'));
    }
}
<?php namespace MotionArray\Composers;

use MotionArray\Models\StaticData\Helpers\CategoryTypesWithRelationsHelper;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;

class CategoriesComposer
{
    /**
     * @var CategoryTypesWithRelationsHelper
     */
    protected $categoryTypesWithRelationsHelper;

    /**
     * @var CategoriesWithRelationsHelper
     */
    protected $categoriesWithRelationsHelper;

    public function __construct(
        CategoryTypesWithRelationsHelper $categoryTypesWithRelationsHelper,
        CategoriesWithRelationsHelper $categoriesWithRelationsHelper)
    {
        $this->categoryTypesWithRelationsHelper = $categoryTypesWithRelationsHelper;
        $this->categoriesWithRelationsHelper = $categoriesWithRelationsHelper;
    }

    public function compose($view)
    {
        $categoryTypes = $this->categoryTypesWithRelationsHelper->categoryTypesWithCategories(null, true);
        $categories = $this->categoriesWithRelationsHelper->categories(null, true);

        $view->with(compact('categories', 'categoryTypes'));
    }
}
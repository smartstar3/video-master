<?php namespace MotionArray\Repositories;

use MotionArray\Models\Category;
use MotionArray\Models\SubCategory;

/**
 * Class BrowseRepository
 *
 * @package MotionArray\Repositories\Browse
 */
class BrowseRepository
{
    /**
     * Gets the secondary content page
     *
     * @param $request
     * @return $last_segment
     */
    public function getSecondaryContentPage($request)
    {
        $last_segment = $request->segment(count($request->segments()));

        if (!$request->has('categories')) {
            return $last_segment;
        } else {
            if ($last_segment != 'browse' || preg_match('(,|:)', $request->get('categories')) === 1) {
                return 'browse';
            } else {
                return $request->get('categories');
            }
        }

    }

    public function getCategoryData($categorySlug, $subCategorySlug, $request)
    {
        $category = null;
        $subCategory = null;

        if ($categorySlug) {
            $category = Category::query()
                ->where("slug", "=", $categorySlug)
                ->first();
        }
        if ($category && $subCategorySlug) {
            $subCategory = SubCategory::query()
                ->where('category_id', '=', $category->id)
                ->where("slug", "=", $subCategorySlug)
                ->first();
        }

        return [
            'category' => $category,
            'subcategory' => $subCategory
        ];
    }

    /**
     * @param $category
     * @return string
     */
    public function getOGImage($category)
    {
        $productRepository = app()->make('MotionArray\Repositories\Products\ProductRepository');
        $ogImage = '';

        $categoryId = null;
        if ($category) {
            $categoryId = $category->id;
        }

        $products = $productRepository->getProducts(1, 1, null, 'published_at', 'desc', $categoryId);
        $ogProduct = $products->first();

        if ($ogProduct) {
            $ogImage = $ogProduct->meta['placeholder_image'];
        }

        return $ogImage;
    }
}

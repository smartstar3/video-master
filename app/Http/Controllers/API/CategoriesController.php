<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Support\Facades\Cache;
use MotionArray\Models\Category;

class CategoriesController extends BaseController
{
    public function index()
    {

        $categories = Cache::remember('categories', 240, function () {
            return Category::with(array('subCategories' => function ($query) {
                $query->orderBy('sidebar_order', 'ASC');
            }))->orderBy('sidebar_order')->get();
        });

        return $categories;
    }
}

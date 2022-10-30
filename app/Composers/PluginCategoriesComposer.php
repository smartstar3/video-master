<?php namespace MotionArray\Composers;

use MotionArray\Models\Category;
use MotionArray\Models\CategoryGroup;
use MotionArray\Models\CategoryType;

class PluginCategoriesComposer
{
    public function compose($view)
    {
        $categoryGroups = CategoryGroup::with('pluginCategories')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')->get();

        $categoryGroups = $categoryGroups->filter(function ($categoryGroup) {
            return $categoryGroup->pluginCategories->count();
        });

        $view->with('categoryGroups', $categoryGroups);
    }
}

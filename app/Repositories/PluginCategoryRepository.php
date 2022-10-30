<?php namespace MotionArray\Repositories;

use MotionArray\Models\PluginCategory;
use MotionArray\Repositories\EloquentBaseRepository;

class PluginCategoryRepository extends EloquentBaseRepository
{
    public function __construct(PluginCategory $pluginCategory)
    {
        $this->model = $pluginCategory;
    }
}

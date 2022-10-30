<?php namespace MotionArray\Repositories;

use MotionArray\Models\CategoryGroup;
use MotionArray\Repositories\EloquentBaseRepository;

class CategoryGroupRepository extends EloquentBaseRepository
{
    public function __construct(CategoryGroup $categoryGroup)
    {
        $this->model = $categoryGroup;
    }
}

<?php

namespace MotionArray\Repositories;

use JesusRugama\Craft\CraftHelper;

abstract class CraftCategoryRepository
{
    protected $craftHelper;
    protected $group = null;

    public function __construct(CraftHelper $craftHelper)
    {
        $this->craftHelper = $craftHelper;
    }

    public function findCategoriesInPath($path)
    {
        if (!$path) {
            return;
        }

        // Returns
        $pathCategories = [];

        $segments = explode('/', $path);

        $match = null;

        foreach ($segments as $i => $segment) {
            if (!$i) {
                $categories = $this->getCategoriesByLevel();
            } elseif ($match) {
                $categories = $match->children;

                if (!is_array($categories)) {
                    $categories = $categories->all();
                }
            }

            $matches = array_values(array_filter($categories, function ($category) use ($segment) {
                return $category->slug == $segment;
            }));

            if (count($matches)) {
                $match = $matches[0];

                $pathCategories[] = $match;
            } else {
//                $pathCategories[] = null;

                break;
            }
        }

        return $pathCategories;
    }

    public function getCategoriesByLevel($level = 1)
    {
        return $this->craftHelper->getCategories($this->group, ['level' => $level]);
    }
}
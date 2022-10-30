<?php

namespace MotionArray\Services\Algolia;

use Illuminate\Support\Facades\Auth;
use MotionArray\Models\Category;
use MotionArray\Models\StaticData\Resolutions;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;

class AlgoliaClientDataService
{
    /**
     * @var CategoriesWithRelationsHelper
     */
    private $categoriesWithRelationsHelper;

    public function __construct(CategoriesWithRelationsHelper $categoriesWithRelationsHelper)
    {
        $this->categoriesWithRelationsHelper = $categoriesWithRelationsHelper;
    }

    public function browseMetaData(): array
    {
        $categories = $this->categoriesWithRelationsHelper->categoriesWithSubCategoriesAndVersions(Auth::user(), true);

        $categories = $categories
            ->map(function (Category $category) {
                $keys = [
                    'id',
                    'slug',
                    'name',
                    'short_name',
                    'has_resolutions',
                    'has_bpms',
                    'has_versions',
                ];

                $category = $category->toArray();

                $item = array_only($category, $keys);

                $subCategories = collect($category['sub_categories'])
                    ->sortBy('sidebar_order')
                    ->values()
                    ->toArray();

                $item['sub_categories'] = $this->onlySlugAndName($subCategories);
                $item['versions'] = $this->onlySlugAndName($category['versions'])
                    ->sortBy('order')
                    ->values()
                    ->toArray();

                return $item;
            })
            ->sortBy('sidebar_order');

        $categories->push([
            'slug' => 'free',
            'name' => 'Free Items',
            'sub_categories' => [],
            'has_bpms' => false,
            'has_versions' => false,
            'has_resolutions' => false
        ]);

        $resolutions = (new Resolutions())->dataCollection();
        $resolutions = $this->onlySlugAndName($resolutions)
            ->sortBy('order');

        return [
            'categories' => $categories->values(),
            'resolutions' => $resolutions->values(),
        ];
    }

    private function onlySlugAndName($items)
    {
        return collect($items)
            ->map(function ($item) {
                return [
                    'slug' => $item['slug'],
                    'name' => $item['name'],
                ];
            });
    }
}

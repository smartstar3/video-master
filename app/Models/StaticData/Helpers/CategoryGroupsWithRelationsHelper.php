<?php

namespace MotionArray\Models\StaticData\Helpers;

use MotionArray\Models\StaticData\CategoryGroups;
use MotionArray\Models\StaticData\FeatureFlags;
use MotionArray\Services\FeatureFlag\FeatureFlagService;
use MotionArray\Models\CategoryGroup;

class CategoryGroupsWithRelationsHelper
{
    /**
     * @var FeatureFlagService
     */
    protected $featureFlagService;

    public function __construct(
        FeatureFlagService $featureFlagService)
    {
        $this->featureFlagService = $featureFlagService;
    }

    public function categoryGroupsWithCategories($user = null, $isRelatedMarketplace = false)
    {
        $categoryGroups = CategoryGroup::with('categories')->orderBy('name')->get();

        return $this->filterCategoryGroups($categoryGroups, $user, $isRelatedMarketplace);
    }

    protected function filterCategoryGroups($categoryGroups, $user, $isRelatedMarketplace)
    {
        $excludeCategoryGroupIds = $this->excludeCategoryGroupIds($user, $isRelatedMarketplace);

        $filteredCategoryGroupArray = $categoryGroups->filter(function ($categoryGroup) use ($excludeCategoryGroupIds) {
                return !in_array($categoryGroup['id'], $excludeCategoryGroupIds);
            })
            ->values();

        return $filteredCategoryGroupArray;
    }

    protected function excludeCategoryGroupIds($user = null, $isRelatedMarketplace): array
    {
        $excludeCategoryGroupIds = [];
        $isEnableStockPhotoFeature = $this->featureFlagService->check(FeatureFlags::STOCK_PHOTOS, $user, $isRelatedMarketplace);

        if (!$isEnableStockPhotoFeature) {
            $excludeCategoryGroupIds[] = CategoryGroups::IMAGES_ID;
        }

        return $excludeCategoryGroupIds;
    }
}
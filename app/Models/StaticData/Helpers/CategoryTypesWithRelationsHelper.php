<?php

namespace MotionArray\Models\StaticData\Helpers;

use MotionArray\Models\StaticData\CategoryTypes;
use MotionArray\Models\StaticData\FeatureFlags;
use MotionArray\Services\FeatureFlag\FeatureFlagService;
use MotionArray\Models\CategoryType;

class CategoryTypesWithRelationsHelper
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

    public function categoryTypesWithCategories($user = null, $isRelatedMarketplace = false)
    {
        $categoryTypes = CategoryType::with('categories')->orderBy('name')->get();

        return $this->filterCategoryTypes($categoryTypes, $user, $isRelatedMarketplace);
    }

    protected function filterCategoryTypes($categoryTypes, $user, $isRelatedMarketplace)
    {
        $excludeCategoryTypeIds = $this->excludeCategoryTypeIds($user, $isRelatedMarketplace);

        $filteredCategoryTypeArray = $categoryTypes->filter(function ($categoryType) use ($excludeCategoryTypeIds) {
                return !in_array($categoryType['id'], $excludeCategoryTypeIds);
            })
            ->values();

        return $filteredCategoryTypeArray;
    }

    protected function excludeCategoryTypeIds($user = null, $isRelatedMarketplace): array
    {
        $excludeCategoryTypeIds = [];
        $isEnableStockPhotoFeature = $this->featureFlagService->check(FeatureFlags::STOCK_PHOTOS, $user, $isRelatedMarketplace);

        if (!$isEnableStockPhotoFeature) {
            $excludeCategoryTypeIds[] = CategoryTypes::IMAGES_ID;
        }

        return $excludeCategoryTypeIds;
    }
}
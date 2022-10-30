<?php

namespace MotionArray\Models\StaticData\Helpers;

use Illuminate\Support\Collection;
use MotionArray\Models\Category;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\FeatureFlags;
use MotionArray\Models\StaticData\SubCategories;
use MotionArray\Models\StaticData\Versions;
use MotionArray\Services\FeatureFlag\FeatureFlagService;

class CategoriesWithRelationsHelper
{
    /**
     * @var Collection
     */
    protected $categories;

    /**
     * @var Collection
     */
    protected $subCategories;

    /**
     * @var Collection
     */
    protected $versions;

    /**
     * @var FeatureFlagService
     */
    protected $featureFlagService;

    public function __construct(
        FeatureFlagService $featureFlagService,
        Categories $categories,
        SubCategories $subCategories,
        Versions $versions)
    {
        $this->featureFlagService = $featureFlagService;
        $this->categories = $categories->dataCollection();
        $this->subCategories = $subCategories->dataCollection();
        $this->versions = $versions->dataCollection();
    }

    public function categories($user = null, $isRelatedMarketplace = false): Collection
    {
        return $this->categoriesWithRelations(
            $user,
            false,
            false,
            false,
            $isRelatedMarketplace
        );
    }

    public function categoriesWithSubCategoriesAndVersions($user = null, $isRelatedMarketplace = false): Collection
    {
        return $this->categoriesWithRelations(
            $user,
            true,
            true,
            false,
            $isRelatedMarketplace
        );
    }

    public function categoriesWithSubcategoriesAndVersionsWithTrashed($user = null, $isRelatedMarketplace = false): Collection
    {
        return $this->categoriesWithRelations(
            $user,
            true,
            true,
            true,
            $isRelatedMarketplace
        );
    }

    public function categoriesWithVersions($user = null, $isRelatedMarketplace = false): Collection
    {
        return $this->categoriesWithRelations(
            $user,
            false,
            true,
            false,
            $isRelatedMarketplace
        );
    }

    public function categoriesWithSubCategories($user = null, $isRelatedMarketplace = false): Collection
    {
        return $this->categoriesWithRelations(
            $user,
            true,
            false,
            false,
            $isRelatedMarketplace);
    }

    public function excludeCategoryIds($user = null, $isRelatedMarketplace = false): array
    {
        $excludeCategoryIds = [];
        $isEnableStockPhotoFeature = $this->featureFlagService->check(
            FeatureFlags::STOCK_PHOTOS,
            $user,
            $isRelatedMarketplace
        );

        if (!$isEnableStockPhotoFeature) {
            $excludeCategoryIds[] = Categories::STOCK_PHOTOS_ID;
        }

        return $excludeCategoryIds;
    }

    protected function categoriesWithRelations(
        $user,
        $includeSubCategories = false,
        $includeVersions = false,
        $includeTrashedSubCategories = false,
        $isRelatedMarketplace
    ): Collection
    {
        $categories = $this->categories
            ->sortBy('sidebar_order')
            ->map(function (array $category) use (
                $includeSubCategories,
                $includeTrashedSubCategories,
                $includeVersions
            ) {
                $categoryId = $category['id'];
                $relations = [];

                if ($includeSubCategories) {
                    $relations['sub_categories'] = $this->subCategories($categoryId, $includeTrashedSubCategories);
                }

                if ($includeVersions) {
                    $relations['versions'] = [];

                    if ($category['has_versions']) {
                        $relations['versions'] = $this->versions($categoryId);
                    }
                }

                $category = array_merge($category, $relations);

                return $category;
            })
            ->values()
            ->toArray();

        return $this->filterCategories($categories, $user, $isRelatedMarketplace);
    }

    protected function filterCategories(array $categories, $user, $isRelatedMarketplace): Collection
    {
        $excludeCategoryIds = $this->excludeCategoryIds($user, $isRelatedMarketplace);

        $filteredCategoryArray = collect($categories)
            ->filter(function ($category) use ($excludeCategoryIds) {
                return !in_array($category['id'], $excludeCategoryIds);
            })
            ->values()
            ->toArray();

        return collect($filteredCategoryArray)->map(function ($category) {
            return new Category($category);
        });
    }

    protected function subCategories($categoryId, $includeTrashed = false): array
    {
        return $this->subCategories
            ->where('category_id', $categoryId)
            ->when(!$includeTrashed, function (Collection $collection) {
                return $collection->where('deleted_at', '===', null);
            })->sortBy('order')
            ->values()
            ->toArray();
    }

    protected function versions($categoryId): array
    {
        $versionIds = (new Categories)->categoryIdToVersionIds()[$categoryId];
        return $this->versions->whereIn('id', $versionIds)
            ->sortByDesc('order')
            ->values()
            ->toArray();
    }
}

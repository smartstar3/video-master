<?php

namespace MotionArray\Services\Algolia;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use MotionArray\Models\SellerFollower;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\Resolutions;
use MotionArray\Models\StaticData\SubCategories;
use MotionArray\Models\StaticData\Versions;
use MotionArray\Repositories\ProductImpressionRepository;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;

class AlgoliaSearchRequestAdapter
{
    /**
     * @var Versions
     */
    protected $versionsData;

    /**
     * @var Resolutions
     */
    protected $resolutionsData;

    /**
     * @var Categories
     */
    protected $categoriesData;

    /**
     * @var SubCategories
     */
    protected $subCategoriesData;

    /**
     * @var ProductImpressionRepository
     */
    protected $productImpressionRepo;

    /**
     * @var CategoriesWithRelationsHelper
     */
    protected $categoriesWithRelationsHelper;

    public function __construct(
        Versions $versionsData,
        Resolutions $resolutionsData,
        Categories $categoriesData,
        SubCategories $subCategoriesData,
        ProductImpressionRepository $productImpressionRepo,
        CategoriesWithRelationsHelper $categoriesWithRelationsHelper
    )
    {
        $this->versionsData = $versionsData;
        $this->resolutionsData = $resolutionsData;
        $this->categoriesData = $categoriesData;
        $this->subCategoriesData = $subCategoriesData;
        $this->productImpressionRepo = $productImpressionRepo;
        $this->categoriesWithRelationsHelper = $categoriesWithRelationsHelper;
    }

    public function build(AlgoliaSearchRequest $request): array
    {
        $data = $request->attributes();
        $categoryFilters = $data['categoryFilters'];
        $sortBy = $data['sortBy'];
        $dateAdded = $data['dateAdded'];
        $perPage = $data['perPage'];
        $page = $data['page'] - 1;
        $optionalWords = $data['optionalWords'];
        $optionalWords = implode(',', $optionalWords);
        $searchTags = $data['searchTags'];
        $filters = $data['filters'];
        $excludeProductIds = $data['excludeProductIds'];
        $onlyProductIds = $data['onlyProductIds'];

        $categorySlugs = array_keys($categoryFilters);
        $categories = $this->categoriesData->dataCollection()
            ->whereIn('slug', $categorySlugs);

        $categoryFilters = $this->prepareCompatibleVersions($categoryFilters);

        $facetFilters = [];
        $includeCategoryFacets = $this->includeCategoryFacets($categories, $categoryFilters);
        if ($includeCategoryFacets) {
            $facetFilters[] = $includeCategoryFacets;
        }

        $excludeCategoryIdFacets = $this->excludeCategoryIdFacets();
        if ($excludeCategoryIdFacets) {
            $facetFilters = array_merge($facetFilters, $excludeCategoryIdFacets);
        }

        $preparedFilters = $this->filtersToFacets($filters);
        if ($preparedFilters) {
            $facetFilters = array_merge($facetFilters, $preparedFilters);
        }

        $specFacets = $this->specFacets($categories, $categoryFilters);
        if ($specFacets) {
            $facetFilters = array_merge($facetFilters, $specFacets);
        }

        if ($excludeProductIds) {
            $facetFilters[] = $this->excludeProductIdFacets($excludeProductIds);
        }
        if ($onlyProductIds) {
            $facetFilters[] = $this->onlyProductIdFacets($onlyProductIds);
        }


        $numericFilters = [];
        $dateAddedFilter = $this->dateAddedFilter($dateAdded);
        if ($dateAddedFilter) {
            $numericFilters[] = $dateAddedFilter;
        }

        $index = $this->index($sortBy);

        return [
            'index' => $index,
            'searchTags' => $searchTags,
            'options' => [
                'facetFilters' => $facetFilters,
                'numericFilters' => $numericFilters,
                'optionalWords' => $optionalWords,
                'hitsPerPage' => $perPage,
                'page' => $page,
            ],
        ];
    }

    protected function index($sortBy)
    {
        $baseIndex = Config::get('algolia.index');

        $map = [
            'newest' => $baseIndex,
            'most-popular' => $baseIndex . '_by_downloads',
            'kick-ass' => $baseIndex . '_by_kickass',
        ];

        return array_get($map, $sortBy, $baseIndex);
    }

    protected function includeCategoryFacets(Collection $categories, array $categoryFilters)
    {
        $categories = $categories->map(function (array $category) use ($categoryFilters) {
            $categorySlug = $category['slug'];
            $subCategorySlugs = array_keys($categoryFilters[$categorySlug]['subCategories']);
            // allow queries with legacy slugs
            $subCategorySlugs = collect($subCategorySlugs)
                ->map(function ($slug) use ($categorySlug) {
                    return SubCategories::normalizeSlug($categorySlug, $slug);
                })
                ->toArray();

            // include all subcategories
            $subCategories = $this->subCategoriesData->dataCollection()->where('category_id', $category['id']);
            if ($subCategorySlugs) {
                // include only requested subcategories
                $subCategories = $subCategories->whereIn('slug', $subCategorySlugs);
            }

            $category['subCategories'] = $subCategories;
            return $category;
        });

        return $categories->flatMap(function (array $category) {
            return $category['subCategories']->map(function (array $subCategory) use ($category) {
                return "categories: {$category['name']} > {$subCategory['name']}";
            });
        })->toArray();
    }

    protected function excludeCategoryIdFacets()
    {
        $excludeCategoryIds = $this->categoriesWithRelationsHelper->excludeCategoryIds(Auth::user(), true);

        if (!count($excludeCategoryIds)) {
            return;
        }

        $excludeCategoryIdFacets = collect($excludeCategoryIds)->map(function ($categoryId) {
            return "category.id: -{$categoryId}";
        })->toArray();

        return $excludeCategoryIdFacets;
    }

    protected function specFacets(Collection $categories, array $categoryFilters)
    {
        $specStrings = [];
        foreach ($categories as $category) {
            $categoryFilter = $categoryFilters[$category['slug']];
            $resolution = $categoryFilter['resolution'];
            $versions = $categoryFilter['versions'];
            $bpms = $categoryFilter['bpms'];
            $durations = $categoryFilter['durations'];

            $prefix = "specs.cat{$category['id']}";

            if ($resolution) {
                $resolution = $this->resolutionsData->find($resolution['slug']);
                $specStrings[] = [
                    "{$prefix}.resolution:none",
                    "{$prefix}.resolution:{$resolution['name']}"
                ];
            }

            if ($versions) {
                $versionStrings = collect($versions)
                    ->map(function ($version) use ($prefix) {
                        $version = $this->versionsData->find($version['slug']);
                        return "{$prefix}.version:{$version['name']}";
                    })
                    ->push("{$prefix}.version:none")
                    ->toArray();
                $specStrings[] = $versionStrings;
            }

            if ($bpms) {
                $specStrings[] = collect($bpms)
                    ->map(function ($bpm) use ($prefix) {
                        return "{$prefix}.bpm:{$bpm}";
                    })
                    ->toArray();
            }

            if ($durations) {
                $specStrings[] = collect($durations)
                    ->map(function ($duration) use ($prefix) {
                        return "{$prefix}.duration:{$duration}";
                    })
                    ->toArray();
            }
        }
        return $specStrings;
    }

    protected function filtersToFacets(array $filters): array
    {
        $facets = collect();
        collect($filters)
            ->each(function ($filter) use ($facets) {
                $this->addFilterToFacet($filter, $facets);
            });

        $facets = collect($facets)
            ->filter()
            ->toArray();

        return $facets;
    }

    protected function addFilterToFacet(string $filter, Collection $facets)
    {
        $map = [
            'free' => function ($facets) {
                $facets->push('free:true');
            },
            'requested' => function ($facets) {
                $facets->push('requested:true');
            },
            'recently-viewed' => function ($facets) {
                $this->addLoggedInUserRecentlyViewedFacet($facets);
            },
            'people-i-follow' => function ($facets) {
                $sellerIdFacets = $this->loggedInUserPeopleIFollowFacets();

                $facets->push('owned_by_ma:false');
                if ($sellerIdFacets) {
                    $facets->push($sellerIdFacets);
                }
            },
        ];

        $result = array_get($map, $filter);
        if ($result) {
            $result($facets);
        }
    }

    private function addLoggedInUserRecentlyViewedFacet(Collection $facets)
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }
        $productIds = $this->productImpressionRepo->getMostRecentImpressionProductIds($user->id);
        $recentlyViewedFacet = collect($productIds)
            ->map(function ($productId) {
                return 'objectID: ' . $productId;
            })
            ->toArray();

        if ($recentlyViewedFacet) {
            $facets->push($recentlyViewedFacet);
        }
    }

    protected function loggedInUserPeopleIFollowFacets(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $sellerIdFacets = SellerFollower::query()
            ->where('follower_id', $user->id)
            ->pluck('seller_id')
            // push an unmatchable filter
            // in case the user is not following anyone
            ->push('0')
            ->map(function ($sellerId) {
                return 'seller_id:' . $sellerId;
            })
            ->toArray();

        return $sellerIdFacets;
    }

    protected function dateAddedFilter($dateAdded)
    {
        switch ($dateAdded) {
            case 'last-6-months':
                $value = Carbon::now()->subMonths(6)->timestamp;
                break;
            case 'last-year':
                $value = Carbon::now()->subYears(1)->timestamp;
                break;

            case 'last-month':
                $value = Carbon::now()->subMonths(1)->timestamp;
                break;

            case 'this-week':
                $value = Carbon::now()->subWeek(1)->timestamp;
                break;
            default:
                $value = 0;
                break;
        }
        return 'published_at>' . $value;
    }

    protected function prepareCompatibleVersions(array $categoryFilters): array
    {
        return collect($categoryFilters)
            ->map(function ($filter) {
                $version = $filter['version'];
                $versionSlug = array_get($version, 'slug');
                $versions = [];
                if ($versionSlug) {
                    $versions = $this->versionSlugToCompatibleVersions($versionSlug);
                }
                unset($filter['version']);
                $filter['versions'] = $versions;
                return $filter;
            })
            ->toArray();
    }

    protected function versionSlugToCompatibleVersions($versionSlug)
    {
        $versions = $this->versionsData;
        $versionId = $versions->slugToId($versionSlug);
        $compatibleVersionIds = $versions->getBackwardCompatibleVersionIds($versionId);
        $compatibleVersionIds[] = $versionId;
        return $versions->dataCollection()
            ->whereIn('id', $compatibleVersionIds)
            ->map(function ($version) {
                return array_only($version, ['slug', 'name']);
            })
            ->values()
            ->toArray();
    }

    protected function excludeProductIdFacets($excludeProductIds): array
    {
        return collect($excludeProductIds)
            ->map(function ($productId) {
                return 'objectID:-' . $productId;
            })
            ->toArray();
    }

    protected function onlyProductIdFacets($excludeProductIds): array
    {
        return collect($excludeProductIds)
            ->map(function ($productId) {
                return 'objectID:' . $productId;
            })
            ->toArray();
    }
}

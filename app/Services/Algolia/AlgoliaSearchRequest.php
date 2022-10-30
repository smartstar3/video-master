<?php

namespace MotionArray\Services\Algolia;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;
use MotionArray\Models\StaticData\Resolutions;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use MotionArray\Models\StaticData\SubCategories;
use Auth;

class AlgoliaSearchRequest
{
    protected $attributes = [];

    protected $defaults = [
        'perPage' => 60,
        'page' => 1,
        'categoryFilters' => [],
        'optionalWords' => [],
        'dateAdded' => 'any-time',
        'sortBy' => 'newest',
        'searchTags' => [],
        'filters' => [],
        'excludeProductIds' => [],
        'onlyProductIds' => [],
    ];

    public function __construct(array $data)
    {
        $data = collect($data)
            ->filter(function ($value) {
                return $value !== null;
            })
            ->toArray();

        $data = $this->prepareOnlyProductIdsData($data);
        $keys = array_keys($this->defaults);
        $attributes = array_only($data, $keys);
        $attributes = array_merge($this->defaults, $attributes);

        $attributes['categoryFilters'] = $this->normalizeCategoryFilterSlugs($attributes['categoryFilters']);
        $attributes['categoryFilters'] = collect($attributes['categoryFilters'])
            ->map(function ($row) {
                return $this->prepareCategoryFilter($row);
            })
            ->toArray();
        $this->attributes = $attributes;
    }

    protected function prepareCategoryFilter($row): array
    {
        $defaults = [
            'version' => [],
            'resolution' => [],
            'subCategories' => [],
            'bpms' => [],
            'durations' => [],
        ];
        $keys = array_keys($defaults);
        $row = array_only($row, $keys);
        $row = array_merge($defaults, $row);

        $row['version'] = array_only($row['version'], ['slug']);
        $row['resolution'] = array_only($row['resolution'], ['slug']);
        $row['subCategories'] = collect($row['subCategories'])
            ->map(function ($row) {
                return array_only($row, ['slug']);
            })
            ->toArray();

        return $row;
    }

    public function rules()
    {
        $categoriesData = new Categories();

        $helper = app(CategoriesWithRelationsHelper::class);
        $categories = $helper->categoriesWithSubCategoriesAndVersions(Auth::user(), true);

        $rules = [];
        $categoryFilters = $this->attributes['categoryFilters'];

        $resolutionSlugs = (new Resolutions())->dataCollection()
            ->pluck('slug')
            ->toArray();

        foreach ($categoryFilters as $categorySlug => $row) {
            $categorySlug = Categories::normalizeSlug($categorySlug);
            $category = $categories->first(function($category) use ($categorySlug){
                return $category['slug'] === $categorySlug;
            });

            if(!$category) {
                continue;
            }

            $category = $category->toArray();
            $subCategories = $category['sub_categories'];

            $rules["categoryFilters.{$categorySlug}.subCategories"] = 'array';

            foreach ($subCategories as $subCategory) {
                $subCategorySlug = $subCategory['slug'];
                $subCategorySlug = SubCategories::normalizeSlug($categorySlug, $subCategorySlug);

                $rules["categoryFilters.{$categorySlug}.subCategories.{$subCategorySlug}.slug"] = Rule::in([$subCategorySlug]);
            }

            if ($category['has_versions']) {
                $validVersionSlugs = collect($category['versions'])->pluck('slug')->toArray();
                $rules["categoryFilters.{$categorySlug}.version.slug"] = Rule::in($validVersionSlugs);
            }

            if ($category['has_resolutions']) {
                $rules["categoryFilters.{ $categorySlug}.resolution.slug"] = Rule::in($resolutionSlugs);
            }

            if ($category['has_bpms']) {
                $rules["categoryFilters.{$categorySlug}.bpms"] = 'array';
                $rules["categoryFilters.{$categorySlug}.bpms.*"] = 'integer';

                $rules['categoryFilters.' . $categorySlug . '.durations'] = 'array';
                // validate duration format mm:ss
                $rules['categoryFilters.' . $categorySlug . '.durations.*'] = 'regex:/^(\d{1,3}:[0-5]\d)$/i';
            }
        }

        return array_merge($rules, [
            'categoryFilters' => 'array',
            'sortBy' => Rule::in([
                'newest',
                'most-popular',
                'kick-ass',
            ]),
            'dateAdded' => Rule::in([
                'any-time',
                'last-6-months',
                'last-year',
                'last-month',
                'this-week',
            ]),
            'filters' => [
                'nullable',
                'array',
                Rule::in([
                    'requested',
                    'recently-viewed',
                    'people-i-follow',
                    'free'
                ])
            ],
            'excludeProductIds' => 'array',
            'onlyProductIds' => 'array',
            'optionalWords' => 'array',
            'page' => [
                'integer',
                'min:0'
            ],
            'perPage' => [
                'integer',
                'max:60'
            ]
        ]);
    }

    public function validate()
    {
        $validator = $this->validator();

        $validator->after(function ($validator) {
            $invalidCategorySlugs = $this->getInvalidCategorySlugs();
            if ($invalidCategorySlugs) {
                $message = 'The following category slugs are invalid: ' . implode(', ', $invalidCategorySlugs);
                $validator->errors()->add('categoryFilter', $message);
            }
        });

        $validator->validate();
    }

    protected function getInvalidCategorySlugs()
    {
        $categorySlugs = array_keys($this->attributes['categoryFilters']);
        $validSlugs = app(Categories::class)->dataCollection()->pluck('slug')->toArray();

        return collect($categorySlugs)
            ->filter(function ($categorySlug) use ($validSlugs) {
                $categorySlug = Categories::normalizeSlug($categorySlug);

                return !in_array($categorySlug, $validSlugs);
            })
            ->toArray();
    }

    public function attributes()
    {
        $this->validate();
        return $this->attributes;
    }

    public function validator(): Validator
    {
        /** @var ValidationFactory $factory */
        $factory = app(ValidationFactory::class);

        return $factory->make($this->attributes, $this->rules());
    }

    protected function prepareOnlyProductIdsData(array $data): array
    {
        // if the 'onlyProductIds' key is set to an empty array
        if (
            array_key_exists('onlyProductIds', $data) &&
            is_array($data['onlyProductIds']) &&
            !count($data['onlyProductIds'])
        ) {
            // add zero so the filter will return no results
            $data['onlyProductIds'] = [0];
        }
        return $data;
    }

    public function hasUserSpecificFilters(): bool
    {
        // this is also checked for front end caching here web-client/src/store.js
        $userSpecificFilters = [
            'recently-viewed',
            'people-i-follow',
        ];

        $filters = $this->attributes['filters'];
        foreach ($userSpecificFilters as $userSpecificFilter) {
            if (in_array($userSpecificFilter, $filters)) {
                return true;
            }
        }
        return false;
    }

    protected function normalizeCategoryFilterSlugs(array $categoryFilters): array
    {
        foreach ($categoryFilters as $categorySlug => $row) {
            $newSlug = Categories::normalizeSlug($categorySlug);

            if ($categorySlug != $newSlug) {
                unset($categoryFilters[$categorySlug]);
                $categoryFilters[$newSlug] = $row;
            }
        }

        return $categoryFilters;
    }
}

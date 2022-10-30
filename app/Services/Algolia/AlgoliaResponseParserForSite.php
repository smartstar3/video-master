<?php

namespace MotionArray\Services\Algolia;

use Illuminate\Support\Carbon;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Services\PreviewFile\PreviewFileService;
use MotionArray\Support\Pagination\JsonPaginator;

class AlgoliaResponseParserForSite extends AlgoliaResponseParser
{
    /**
     * @var PreviewFileService
     */
    private $previewFileService;

    public function __construct(PreviewFileService $previewFileService)
    {
        $this->previewFileService = $previewFileService;

        parent::__construct();
    }

    public function responseToArray(array $results): array
    {
        $products = $results['products'];
        $total = $results['total'];
        $currentPage = $results['currentPage'];
        $perPage = $results['perPage'];

        return [
            'algolia_index' => $results['index'],
            'algolia_options' => $results['options'],
            'algolia_search_tags' => $results['searchTags'],

            'products' => $this->prepareProducts($products),
            'meta' => (new JsonPaginator($products, $total, $perPage, $currentPage))->toArray()
        ];
    }

    public function prepareProducts(array $products)
    {
        return collect($products)
            ->map(function (array $product) {
                $keys = [
                    'name',
                    'description',
                    'seller_id',
                    'audio_placeholder',
                    'free',
                    'owned_by_ma',
                    'slug',
                    'published_at',
                    'downloads',
                    'requested',
                    'is_music',
                    'previews_files',
                    'is_kick_ass'
                ];

                if ($this->useImgix) {
                    $placeholder = $product['placeholder'];
                } else {
                    $placeholder = $product['placeholder_fallback'];
                }

                $item = array_only($product, $keys);
                $item['previews_files'] = $this->previewFileService->preparePreviewFiles($item['previews_files'], $item['is_music']);

                $product['category']['slug'] = Categories::normalizeSlug($product['category']['slug']);

                $categorySlug = $product['category']['slug'];
                $productSlug = $product['slug'];
                $url = "/{$categorySlug}/{$productSlug}";

                $publishedAt = $product['published_at'];
                $publishedAt = Carbon::createFromTimestamp($publishedAt);
                $isNew = $publishedAt->diffInDays() <= 30;

                return array_merge($item, [
                    'id' => $product['objectID'],
                    'placeholder' => $placeholder,
                    'url' => $url,
                    'is_new' => $isNew,
                    'category' => $product['category']
                ]);
            })
            ->toArray();
    }
}

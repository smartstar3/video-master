<?php

namespace MotionArray\Services\Algolia;

use MotionArray\Models\StaticData\Categories;
use MotionArray\Support\Pagination\JsonPaginator;
use MotionArray\Services\PreviewFile\PreviewFileService;

class AlgoliaResponseParserForAdobePanel extends AlgoliaResponseParser
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
        $perPage = $results['perPage'];
        $products = $results['products'];
        $total = $results['total'];
        $currentPage = $results['currentPage'];

        return [
            'products' => $this->prepareProducts($products),
            'meta' => (new JsonPaginator($products, $total, $perPage, $currentPage))->toArray()
        ];
    }

    protected function prepareProducts(array $products)
    {
        $categoryData = new Categories();

        return collect($products)
            ->map(function (array $product) use ($categoryData) {
                $keys = [
                    'name',
                    'description',
                    'seller_id',
                    'music_id',
                    'free',
                    'audio_placeholder',
                    'slug',
                    'category',
                    'is_editorial_use'
                ];

                $item = array_only($product, $keys);
                $item['category'] = $item['category']['name'];
                $type = $product['is_music'] ? 'audio' : 'video';

                if ($this->useImgix) {
                    $placeholderUrl = $product['placeholder'];
                } else {
                    $placeholderUrl = $product['placeholder_fallback'];
                }

                $source = $this->previewFileService->preparePreviewFiles($product['previews_files'], $product['is_music']);
                $product['category']['slug'] = Categories::normalizeSlug($product['category']['slug']);
                $categoryId = $categoryData->slugToId($product['category']['slug']);

                $specs = [];
                if ($categoryId) {
                    $specsKeys = [
                        'fps',
                        'bpm',
                        'resolution',
                        'plugins',
                        'compression',
                        'duration',
                        'format',
                        'sampleRate'
                    ];
                    $specs = array_only($product['specs']["cat{$categoryId}"], $specsKeys);
                }
                return array_merge($item, [
                    'id' => $product['objectID'],
                    'poster' => $placeholderUrl,
                    'source' => $source,
                    'type' => $type,
                    'specs' => $specs,
                ]);
            })
            ->toArray();
    }
}

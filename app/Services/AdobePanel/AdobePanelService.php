<?php

namespace MotionArray\Services\AdobePanel;

use Config;
use MotionArray\Helpers\Imgix;
use MotionArray\Models\User;
use MotionArray\Models\Product;
use MotionArray\Repositories\DownloadRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Services\Algolia\AlgoliaSearchService;
use MotionArray\Services\PreviewFile\PreviewFileService;
use MotionArray\Support\Pagination\JsonPaginator;

class AdobePanelService
{
    /**
     * @var DownloadRepository
     */
    private $downloadRepo;

    /**
     * @var ProductRepository
     */
    private $productRepo;

    /**
     * @var AlgoliaSearchService
     */
    private $algoliaSearchService;

    /**
     * @var PreviewFileService
     */
    private $previewFileService;

    public function __construct(
        DownloadRepository $downloadRepo,
        ProductRepository $productRepo,
        AlgoliaSearchService $algoliaSearchService,
        PreviewFileService $previewFileService
    )
    {
        $this->downloadRepo = $downloadRepo;
        $this->productRepo = $productRepo;
        $this->algoliaSearchService = $algoliaSearchService;
        $this->previewFileService = $previewFileService;
    }

    public function searchProducts(array $attributes)
    {
        $results = $this->algoliaSearchService->searchForAdobePanel($attributes);

        $results['products'] = collect($results['products'])->map(function ($product) {
            $product['specs'] = $this->removeSuffixFromSpecs($product['specs']);
            return $product;
        });

        return $results;
    }

    public function userDownloads(User $user, int $page, int $productsPerPage): array
    {
        $filters = [
            'page' => $page,
            'per_page' => $productsPerPage,
            'with_trashed_products' => false
        ];

        $downloads = $this->downloadRepo->getUserDownloadedProducts($user->id, $filters);
        $totalCount = $this->downloadRepo->getDownloadedProductsCount($user->id, $filters);

        $downloads->load('product');

        $downloadProducts = $downloads->map(function ($download) {
            return $this->prepareProduct($download->product);
        });

        return [
            'products' => $downloadProducts,
            'meta' => (new JsonPaginator($downloadProducts, $totalCount, $productsPerPage, $page))->toArray(),
        ];
    }

    public function prepareProduct(Product $product)
    {
        $useImgix = Config::get('imgix.use_imgix');
        $keys = [
            'id',
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

        $item = array_only($product->toArray(), $keys);
        $item['category'] = $item['category']['name'];
        $type = $product->isAudio() ? 'audio' : 'video';

        $previewFiles = $this->productRepo->getPreviewFiles($product)->toArray();
        $source = $this->previewFileService->preparePreviewFiles($previewFiles, $product->isAudio());
        $specs = $product->getPackageSpec();

        // plugins and duration should be array. except that everything is not array.
        $arraySpecKeys = [
            'plugins'
        ];

        $specs = collect($specs)->map(function ($spec, $key) use ($arraySpecKeys) {
            if (in_array($key, $arraySpecKeys)) {
                $arraySpec = [];
                foreach ($spec as $value) {
                    $arraySpec[] = $value['name'];
                }

                return $arraySpec;
            }

            return $spec[0] ? $spec[0]['name'] : null;
        })->toArray();

        $specs = $this->removeSuffixFromSpecs($specs);

        $trackDurations = $product->trackDurationsToArray();
        if ($trackDurations) {
            $specs['duration'] = $trackDurations;
        }

        $placeholderFallback = $product->present()->getPreview('placeholder', 'low', null, true);

        if ($useImgix) {
            $placeholderUrl = Imgix::getImgixUrl($placeholderFallback, 660);
        } else {
            $placeholderUrl = $placeholderFallback;
        }

        return array_merge($item, [
            'type' => $type,
            'poster' => $placeholderUrl,
            'source' => $source,
            'specs' => $specs
        ]);
    }

    public function removeSuffixFromSpecs(array $specs): array
    {
        //we need to remove suffixes from following specs.
        $keys = [
            'bpm',
            'fps',
            'resolution'
        ];

        foreach ($specs as $specKey => $spec) {
            if (in_array($specKey, $keys)) {
                $specs[$specKey] = explode(' ', $spec)[0];
            }

            // remove dot from front when the spec is format
            if ($specKey == 'format') {
                $specs[$specKey] = str_replace('.', '', $spec);
            }

            // remove KHz suffix when the spec is sampleRate
            if ($specKey == 'sampleRate') {
                $specs[$specKey] = str_replace('kHz', '', $spec);
            }
        }
        return $specs;
    }
}

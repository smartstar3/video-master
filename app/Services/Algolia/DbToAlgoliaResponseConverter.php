<?php

namespace MotionArray\Services\Algolia;

use Illuminate\Database\Eloquent\Collection;
use MotionArray\Models\Product;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Services\PreviewFile\PreviewFileService;

class DbToAlgoliaResponseConverter
{
    /**
     * @var PreviewFileService
     */
    private $previewFileService;

    /**
     * @var ProductRepository
     */
    private $productRepo;

    /**
     * @var AlgoliaResponseParserForSite
     */
    private $responseParser;

    public function __construct(
        PreviewFileService $previewFileService,
        ProductRepository $productRepo,
        AlgoliaResponseParserForSite $responseParser
    )
    {
        $this->previewFileService = $previewFileService;
        $this->productRepo = $productRepo;
        $this->responseParser = $responseParser;
    }

    public function prepareProducts(Collection $products): array
    {
        $products = $this->queryProductRelations($products);

        return $this->responseParser->prepareProducts($products);
    }

    protected function queryProductRelations(Collection $products): array
    {
        $products->load([
            'subCategories',
            'tags',
        ]);

        return $products
            ->map(function (Product $product) {
                $previewFiles = $this->productRepo->getPreviewFiles($product)->toArray();

                return array_merge($product->toArray(), [
                    'placeholder' => $product->getPlaceholder(),
                    'placeholder_fallback' => $product->getPlaceholderFallback(),
                    'previews_files' => $previewFiles,
                    'is_music' => $product->isAudio(),
                    'published_at' => $product->published_at->timestamp,
                    'objectID' => $product->id,
                ]);
            })
            ->toArray();
    }

}

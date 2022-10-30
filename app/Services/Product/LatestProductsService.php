<?php

namespace MotionArray\Services\Product;

use Illuminate\Database\Eloquent\Collection;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Services\Algolia\DbToAlgoliaResponseConverter;

class LatestProductsService
{
    /**
     * @var ProductRepository
     */
    private $productRepo;

    /**
     * @var DbToAlgoliaResponseConverter
     */
    private $dbToAlgoliaResponse;

    public function __construct(
        ProductRepository $productRepo,
        DbToAlgoliaResponseConverter $dbToAlgoliaResponse
    )
    {
        $this->productRepo = $productRepo;
        $this->dbToAlgoliaResponse = $dbToAlgoliaResponse;
    }

    public function get(): array
    {
        $map = [
            Categories::AFTER_EFFECTS_TEMPLATES_ID => 3,
            Categories::STOCK_VIDEO_ID => 1,
            Categories::STOCK_MOTION_GRAPHICS_ID => 1,
            Categories::STOCK_MUSIC_ID => 1,
            Categories::PREMIERE_PRO_TEMPLATES_ID => 2,
        ];

        $results = new Collection();
        $page = 1;

        foreach ($map as $categoryId => $itemCount) {
            $results = $results->merge($this->productRepo->getProductsByCategory($categoryId, $page, $itemCount));
        }

        return $this->dbToAlgoliaResponse->prepareProducts($results);
    }
}

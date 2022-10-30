<?php

namespace MotionArray\Console\Commands;

use DB;
use Illuminate\Console\Command;
use MotionArray\Models\Product;
use MotionArray\Services\Algolia\AlgoliaClient;
use MotionArray\Repositories\Products\ProductRepository;

class AlgoliaRemoveTrashedProducts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray:algolia-remove-trashed-products 
        {--seller-id=?} 
        {--product-id=?} 
        {--dry-run}
        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes trashed products from Algolia index';

    /**
     * @var AlgoliaClient
     */
    protected $algolia;

    /**
     * AlgoliaPushSearchData constructor.
     * @param ProductRepository $product
     * @param AlgoliaClient $algolia
     */
    public function __construct(AlgoliaClient $algolia)
    {
        $this->algolia = $algolia;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        /** @var int $chunkSize */
        $chunkSize = 500;

        $trashedProductsQuery = Product::onlyTrashed();

        $sellerId = (int)$this->option('seller-id');
        $productId = (int)$this->option('product-id');
        $dryRun = (int)$this->option('dry-run');

        if (!empty($sellerId)) {
            $trashedProductsQuery->whereSellerId($sellerId);
        }
        if (!empty($productId)) {
            $trashedProductsQuery->whereId($productId);
        }

        $trashedProductsQuery->chunk($chunkSize, function ($products) use ($dryRun) {
            $productIds = $products->pluck('id');
            if (!$dryRun) {
                $this->output->writeln("Removed " . count($productIds) . " products");
                $this->algolia->removeProducts($productIds);
            } else {
                $this->output->writeln("Would have removed " . count($productIds) . " products. (Dry run)");
            }
        });

        $this->output->write(PHP_EOL);
    }
}

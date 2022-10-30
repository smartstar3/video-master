<?php namespace MotionArray\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MotionArray\Models\Product;
use MotionArray\Services\Algolia\AlgoliaClient;
use MotionArray\Repositories\Products\ProductRepository;

class AlgoliaPushSearchData extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray:algolia-push-search-data 
        {--seller-id=?} 
        {--product-id=?} 
        {--kickass-only} 
        {--recent-hours=}
        {--dry-run}
        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the search data to Algolia';

    /**
     * @var ProductRepository
     */
    protected $product;

    /**
     * @var AlgoliaClient
     */
    protected $algolia;

    /**
     * AlgoliaPushSearchData constructor.
     * @param ProductRepository $product
     * @param AlgoliaClient $algolia
     */
    public function __construct(ProductRepository $product, AlgoliaClient $algolia)
    {
        $this->product = $product;
        $this->algolia = $algolia;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $limit = 100;
        $page = 0;

        $sellerId = (int)$this->option('seller-id');
        $productId = (int)$this->option('product-id');
        $recentHours = (int)$this->option('recent-hours');

        $kickass = $this->option('kickass-only');
        $countQuery = Product::where('product_status_id', '=', 1);
        if (!empty($sellerId)) {
            $countQuery->where('seller_id', '=', $sellerId);
        }
        if (!empty($productId)) {
            $countQuery->where('id', '=', $productId);
        }
        if (!empty($recentHours)) {
            $timestamp = Carbon::now()->subHours($recentHours);
            $countQuery->where('products.updated_at', '>=', $timestamp);
        }
        if (!empty($kickass)) {
            $countQuery->where('product_level_id', '=', 1);
        }

        $totalProducts = $countQuery->count();

        $pages = ceil($totalProducts / $limit);

        $this->info('Updating ' . $totalProducts . ' products');

        $progressBar = $this->output->createProgressBar($pages);

        do {
            $page++;

            $productsArray = $this->product->getProductsDataForAlgolia([
                'id' => $productId,
                'kickass' => $kickass,
                'sellerId' => $sellerId,
                'recentHours' => $recentHours,
                'page' => $page,
                'limit' => $limit
            ]);

            if (!$dryRun) {
                $this->algolia->sendProducts($productsArray);
            }

            $progressBar->advance();

        } while (count($productsArray) == $limit);

        $progressBar->finish();
        $this->output->write(PHP_EOL);
    }
}

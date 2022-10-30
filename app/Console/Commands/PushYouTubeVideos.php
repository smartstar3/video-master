<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MotionArray\Models\Product;
use MotionArray\Models\StaticData\ProductStatuses;
use MotionArray\Services\MediaSender\HttpMediaSender;

class PushYouTubeVideos extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray:push-youtube 
        {--amount= : The number of products to send to YouTube. If not specified it will run for all products without YouTube IDs} 
        {--category-id= : If specified, only products in this category will be selected to be sent to YouTube} 
        {--slug= : Send only the product with this slug}
        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends videos to YouTube. Goes through published products without YouTube IDs from newest to oldest.';

    /**
     * @var HttpMediaSender
     */
    private $mediaSender;

    /**
     * Execute the console command.
     * @param HttpMediaSender $mediaSender
     */
    public function handle(HttpMediaSender $mediaSender)
    {
        $this->mediaSender = $mediaSender;

        $categoryId = $this->option('category-id');
        $slug = $this->option('slug');
        $amount = $this->option('amount');

        $productQuery = DB::query()
            ->select('products.id')
            ->from('products')
            ->join('preview_uploads', 'preview_uploads.id',  '=', 'active_preview_id')
            ->where('products.product_status_id', ProductStatuses::PUBLISHED_ID)
            ->whereNull('preview_uploads.youtube_id')
            ->whereNull('products.deleted_at')
            ->orderBy('products.created_at', 'desc')
        ;
        if ($categoryId) {
            $productQuery->where('products.category_id', $categoryId);
        }
        if ($slug) {
            $productQuery->where('products.slug', $slug);
        }
        if ($amount) {
            $productQuery->limit($amount);
        }
        $products = $productQuery->get();

        foreach ($products as $product) {
            $this->info("Product ID: {$product->id}");
            $productModel = Product::whereId($product->id)->first();
            $result = $this->mediaSender->send($productModel);
            $this->info("Result: {$result}");
            $this->info('Done');
        }
        $this->info('End');
    }
}

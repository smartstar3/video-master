<?php namespace MotionArray\Console\Commands\OneTime;

use Illuminate\Console\Command;
use MotionArray\Models\Product;
use AWS;
use MotionArray\Repositories\Products\ProductRepository;
use Config;
/**
 * Fixes XML issues when multiple people edit a product,
 * and the S3 URL is updated on the DB but not on S3 due to concurrency issues.
 * The product URL has to be provided as input.
 *
 * Fetches the product slug DB, finds the real URL on S3,
 * and updates the DB accordingly.
 */
class FixS3XMLErrors extends Command
{
    protected $signature = 'motionarray-onetime:fix-s3-xml-errors';

    protected $description = 'Fixes not found XML errors on S3 for products with invalid download URL.';

    protected $product;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProductRepository $product)
    {
        $this->product = $product;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $count = 0;

        echo "Terminate with Ctrl+D", PHP_EOL;

        // while ( ($url = $this->ask("Enter product URL:")) ) { // Does not support multiline paste.
        while (true) {
            echo "Enter product URL: ";
            $url = trim(fgets(STDIN));
            if (feof(STDIN)) break;

            $slug = @explode("/", $url)[4]; // e.g. https://motionarray.com/category/product-slug-here

            if (!$slug) {
                $this->warn("Invalid product URL {$url}.");
                continue;
            }
            $product = Product::where('slug', '=', $slug)->first();
            if (!$product) {
                $this->warn("No product found with slug '{$slug}'.");
                continue;
            }
            $count += (int) $this->fixProductDownloadUrl($product);
        }


        $this->info("{$count} product packages fixed.");
    }
    /**
     * Fix the download link on S3 if it doesn't match the database
     *
     * @param Product $product
     * @return bool
     */
    protected function fixProductDownloadUrl(Product $product)
    {
        $s3 = AWS::get('s3');
        $bucket = Config::get("aws.packages_bucket");

        $result = $s3->listObjects([
            'Bucket' => $bucket,
            'Prefix' => "motion-array-{$product->id}-", // Don't forget the trailing dash, or 113 will match 1134.
            // 'MaxKeys' => 2,
        ]);
        $packages = $result['Contents'] ?? null;

        if ($packages === null or count($packages) == 0) {
            $this->error("Could not find a package for product id {$product->id}.");
            return false;
        } elseif (count($packages) > 1) {
            $this->error("Found more than 1 package for {$product->id}!"); // Integrity violation
            return false;
        }
        $packageKey = $packages[0]['Key'];
        $filename = pathinfo($packageKey, PATHINFO_FILENAME);
        $extension = pathinfo($packageKey, PATHINFO_EXTENSION);

        if ($filename == $product->package_filename) {
            $this->warn("Product {$product->id} already has valid package information.");
            return false;
        }
        $bucketUrl = Config::get('aws.packages_s3');
        $correctUrl = $bucketUrl . $packageKey;

        $this->info("Fixing product {$product->id}, key changes from '{$product->package_filename}' to '{$packageKey}'.");
        $product->package_file_path = $correctUrl;
        $product->package_filename = $filename;
        $product->package_extension = $extension;
        $product->save();

        return true;
    }
}
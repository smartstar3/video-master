<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Mailers\FeedbackMailer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use MotionArray\Models\Product;
use App;
use AWS;
use Config;
use Carbon\Carbon;

class EmailMissingPackages extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray:email-missing-packages {--limit=-1} {--minutes=?} {--email=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email missing packages';


    /**
     * Submission repo
     */
    private $submissionRepo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mailer = new FeedbackMailer;
        $s3 = AWS::get('s3');
        $bucket = Config::get('aws.packages_bucket');
        $bucketUrl = Config::get('aws.packages_s3');

        $limit = intval($this->option('limit'));
        $email = $this->option('email') == 'true';
        $minutesLimit = intval($this->option('minutes'));

        $submittedAfter = null;
        if ($minutesLimit) {
            $submittedAfter = Carbon::now()->subMinutes($minutesLimit);
        }

        if ($submittedAfter || ($limit < 2000 && $limit > 0)) {
            // If limited, include pending submissions
            $query = Product::whereHas('submission', function ($q) use ($submittedAfter) {
                $q->whereIn('submission_status_id', [2, 3]);

                if ($submittedAfter) {
                    $q->where('submitted_at', '>', $submittedAfter);
                }
            });
        } else {
            // Otherwise get only published products
            $query = Product::where('product_status_id', '=', '1');
        }

        $products = $query->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        $missing = [];
        $fixes = [];
        $found = 0;

        $bar = $this->output->createProgressBar(count($products));

        foreach ($products as $product) {
            $exists = $s3->doesObjectExist($bucket, $product->package_filename . '.' . $product->package_extension);

            if (!$exists) {
                $this->info("Package not found for: $product->id - $product->name");

                $fixed = false;

                // Lets try to find them
                $possibleMatches = $s3->getListObjectsIterator(array(
                    'Bucket' => $bucket,
                    'Prefix' => 'motion-array-' . $product->id
                ))->toArray();

                if (count($possibleMatches) == 1) {
                    // This might be the product package
                    // Lets check if its not on the DB already
                    $match = array_shift($possibleMatches);
                    $packageKey = $match['Key'];

                    $filename = pathinfo($packageKey, PATHINFO_FILENAME);
                    $extension = pathinfo($packageKey, PATHINFO_EXTENSION);

                    $packageExistsInDB = Product::where('package_filename', '=', $filename)
                        ->where('id', '!=', $product->id)
                        ->exists();

                    if (!$packageExistsInDB) {
                        $this->info("Fixing package");
                        // Update product
                        $url = $bucketUrl . $packageKey;

                        // $this->info("Product changed from ".$product->package_file_path. " to ". $url);

                        $product->package_file_path = $url;
                        $product->package_filename = $filename;
                        $product->package_extension = $extension;

                        $product->save();

                        $fixed = true;
                    }
                }

                if ($fixed) {
                    array_push($fixes, $product);
                } else {
                    array_push($missing, $product);
                }
            } else {
                $found++;
            }

            $bar->advance();
        }

        $bar->finish();

        if ($email || count($fixes)) {
            $mailer->missingPackages(compact('found', 'missing', 'fixes'));
        }
    }
}

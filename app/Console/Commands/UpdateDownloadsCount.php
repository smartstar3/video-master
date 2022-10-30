<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MotionArray\Mailers\FeedbackMailer;
use MotionArray\Models\Download;
use MotionArray\Models\ProductDownloadsCount;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use MotionArray\Models\Product;
use App;
use AWS;
use Config;
use Carbon\Carbon;

class UpdateDownloadsCount extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray:update-downloads-count 
        {--days=1 : Number of days since the last download for the products to update}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update download count fields';

    protected $dateRanges;

    public function __construct()
    {
        parent::__construct();

        $this->dateRanges = [
            'downloads_last_month' => Carbon::now()->subMonth(),
            'downloads_last_six_months' => Carbon::now()->subMonths(6),
            'downloads_all_time' => null
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $days = intval($this->option('days'));

        $lastUpdate = Carbon::now()->subDays($days)->startOfDay();

        $chunkSize = 1000;

        /**
         * Get recently downloaded products
         */
        $productsQuery = Download::where('first_downloaded_at', '>', $lastUpdate)
            ->select('product_id')
            ->where('free', '=', 0)
            ->orderBy('id', 'DESC');

        $pages = ceil($productsQuery->count(DB::raw('DISTINCT product_id')) / $chunkSize);

        $progressBar = $this->output->createProgressBar($pages);

        $productsQuery
            ->groupBy('product_id')
            ->chunk($chunkSize, function ($products) use ($progressBar) {

                $productIds = $products->pluck('product_id');

                $productIds->chunk(50)->each(function($productIdSet) {
                    $this->updateProductDownloadsCount($productIdSet);
                });

                $progressBar->advance();
            });

        $progressBar->finish();
    }

    protected function updateProductDownloadsCount($productIds)
    {
        foreach ($this->dateRanges as $key => $startDate) {
            $startDate = $this->dateRanges[$key];

            $downloadCounts = $this->getProductDownloadCounts($productIds, $startDate);

            foreach ($downloadCounts as $downloadCount) {
                ProductDownloadsCount::updateOrCreate(
                    ['product_id' => $downloadCount['product_id']],
                    [$key => $downloadCount['count']]
                );
            }
        }
    }

    /**
     * @param $productIds
     * @param $startDate
     * @return mixed
     */
    protected function getProductDownloadCounts($productIds, $startDate)
    {
        $query = Download::select(DB::raw('product_id, count(*) as count'))
            ->whereIn('product_id', $productIds)
            ->where('downloads.free', '=', 0);

        if ($startDate) {
            $query = $query->where('downloads.first_downloaded_at', '>', $startDate);
        }

        return $query
            ->groupBy('product_id')
            ->get();
    }
}

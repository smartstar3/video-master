<?php namespace MotionArray\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App;
use MotionArray\Models\Download;
use MotionArray\Repositories\DownloadRepository;

class UpdateSellerStatsCache extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:update-seller-stats-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update cache for seller stats';

    protected $download;

    public function __construct(DownloadRepository $download)
    {
        $this->download = $download;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Download::flushCache('seller_stats');
        Download::flushCache('seller_downloads');

        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        for ($m = 1; $m <= $month; $m++) {
            $startDate = Carbon::createFromFormat('d m Y H:i:s', "1 " . $m . " " . $year . " 00:00:00");

            $this->info($startDate);

            $endDate = $startDate->copy()->endOfMonth();

            $this->download->globalDownloadsByCategory($startDate, $endDate, -1);

            $this->download->getDownloadsByCategoryAndSeller($startDate, $endDate, -1);

            $this->download->getWeightForPeriodByCategory($startDate, $endDate); // Caches for 10 minutes
        }
    }
}

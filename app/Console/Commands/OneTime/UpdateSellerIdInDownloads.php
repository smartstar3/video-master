<?php namespace MotionArray\Console\Commands\OneTime;

use Illuminate\Console\Command;
use MotionArray\Models\Download;
use DB;

class UpdateSellerIdInDownloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motionarray-onetime:update-seller-id-in-downloads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates all download records seller_id field';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Download::join(DB::raw('(SELECT products.id, products.seller_id FROM products) p'), function ($join) {
            $join->on('p.id', '=', 'downloads.product_id');
        })
            ->where('first_downloaded_at', '<=', '2018-12-06 00:00:00')
            ->where('first_downloaded_at', '>=', '2018-12-05 00:00:00')
            ->whereNull('downloads.seller_id')
            ->update(['downloads.seller_id' => DB::raw('p.seller_id')]);
    }
}

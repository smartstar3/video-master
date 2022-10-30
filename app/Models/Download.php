<?php namespace MotionArray\Models;

use Carbon\Carbon;
use MotionArray\Support\Database\CacheQueryBuilder;
use MotionArray\Traits\PresentableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Bus;
use MotionArray\Jobs\UpdateUserOnIntercom;

class Download extends BaseModel
{
    use SoftDeletes;

    use PresentableTrait;

    use CacheQueryBuilder;

    protected $presenter = 'MotionArray\Presenters\DownloadPresenter';

    protected $hidden = ['weight'];

    protected $guarded = [];

    protected $dates = ['first_downloaded_at', 'deactivate_at'];

    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }

    public function product()
    {
        return $this->belongsTo('MotionArray\Models\Product');
    }

    public function scopePremium($query)
    {
        return $this->premiumScope($query);
    }

    protected function premiumScope($query)
    {
        return $query->where(function($query) {

            $query->where(function($query){
                $query
                    ->where('first_downloaded_at', '<', '2018-12-01 00:00:00')
                    ->where('downloads.free', '=', 0)
                    ->where('downloads.unlimited', '=', 0);
            })->orWhere(function($query) {
                $query
                    ->where('first_downloaded_at', '>=', '2018-12-01 00:00:00')
                    ->where('downloads.free', '=', 0);
            });
        });
    }
}

Download::creating(function ($download) {
    if (!$download->first_downloaded_at) {
        $download->first_downloaded_at = Carbon::now();
    }

    $product = $download->product;

    $download->free = $product->free;

    $download->weight = $download->free ? 0 : $product->weight;

    $download->unlimited = !$product->free;

    return true;
});

Download::created(function ($download) {
    $buyer = $download->user;

    $seller = $download->product ? $download->product->seller : null;

    Bus::dispatch(new UpdateUserOnIntercom($buyer, false, true));

    if ($seller) {
        Bus::dispatch(new UpdateUserOnIntercom($seller, false, true));
    }
});

<?php

namespace MotionArray\Services\SellerStats;

use Carbon\Carbon;
use MotionArray\Models\User;
use MotionArray\Repositories\DownloadRepository;

class BaseSellerStatsService
{
    protected $download;

    public function __construct(DownloadRepository $downloadRepository)
    {
        $this->download = $downloadRepository;
    }

    /**
     * @param User $seller
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param $cache
     * @return mixed
     */
    public function getSellerDownloadsByCategory(User $seller, Carbon $startDate, Carbon $endDate, $cache)
    {
        $allDownloadsByCategory = $this->download->getDownloadsByCategoryAndSeller($startDate, $endDate, $cache);

        $sellerDownloadsByCategory = $allDownloadsByCategory->where('seller_id', $seller->id);
        return $sellerDownloadsByCategory;
    }
}

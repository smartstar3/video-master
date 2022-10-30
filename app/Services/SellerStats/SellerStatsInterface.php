<?php

namespace MotionArray\Services\SellerStats;

use MotionArray\Models\Country;
use MotionArray\Models\PayoutTotal;
use MotionArray\Models\User;
use Carbon\Carbon;

interface SellerStatsInterface
{
    public function siteStats(Carbon $startDate, Carbon $endDate, $cache = null);

    public function getSellerEarnings(User $seller, Carbon $startDate, Carbon $endDate, PayoutTotal $sitePayout, Country $downloadCountry = null);

    public function sellerStats(User $seller, Carbon $startDate, Carbon $endDate, $cache = null);
}
<?php

namespace MotionArray\Services\Intercom;

use Illuminate\Support\Facades\Bus;
use MotionArray\Jobs\UpdateUserOnIntercom;
use MotionArray\Models\Portfolio;

class IntercomPortfolioObserver
{
    public function created(Portfolio $portfolio)
    {
        $intercom = app(IntercomService::class);

        $intercom->createEvent([
            'event_name' => 'created-portfolio',
            'created_at' => time(),
            'email' => $portfolio->site->user->email
        ]);
    }

    public function saved(Portfolio $portfolio)
    {
        if ($portfolio->site && $portfolio->site->user) {
            Bus::dispatch(new UpdateUserOnIntercom($portfolio->site->user));
        }
    }
}

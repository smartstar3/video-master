<?php

namespace MotionArray\Events;

use Illuminate\Queue\SerializesModels;
use MotionArray\Models\Portfolio;

class PortfolioSaved extends Event
{
    use SerializesModels;

    public $portfolio;

    /**
     * Create a new event instance.
     *
     * @param  Podcast $podcast
     * @return void
     */
    public function __construct(Portfolio $portfolio)
    {
        $this->portfolio = $portfolio;
    }
}
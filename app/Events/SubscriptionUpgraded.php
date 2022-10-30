<?php namespace MotionArray\Events;

use Illuminate\Queue\SerializesModels;
use MotionArray\Models\User;

class SubscriptionUpgraded extends Event
{
    use SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     *
     * @param  Podcast $podcast
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
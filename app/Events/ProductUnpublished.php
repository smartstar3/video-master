<?php namespace MotionArray\Events;

use Illuminate\Queue\SerializesModels;

class ProductUnpublished extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        //
    }
}

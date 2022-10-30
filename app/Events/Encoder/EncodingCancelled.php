<?php namespace MotionArray\Events\Encoder;

use Illuminate\Queue\SerializesModels;
use MotionArray\Events\Event;

class EncondingCancelled extends Event
{
    use SerializesModels;
}

<?php namespace MotionArray\Events\Encoder;

use Illuminate\Queue\SerializesModels;
use MotionArray\Events\Event;

class EncondingDone extends Event
{
    use SerializesModels;
}

<?php namespace MotionArray\Events\Encoder;

use Illuminate\Queue\SerializesModels;
use MotionArray\Events\Event;

class ReadyToEncode extends Event
{
    use SerializesModels;
}

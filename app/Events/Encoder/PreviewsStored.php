<?php namespace MotionArray\Events\Encoder;

use Illuminate\Queue\SerializesModels;
use MotionArray\Events\Event;

class PreviewsStored extends Event
{
    use SerializesModels;
}

<?php namespace MotionArray\Services\Encoding;

use MotionArray\Models\Traits\Uploadable;

interface EncodingInterface
{
    public function encodeVideo(Uploadable $uploadable, $add_watermark);

    public function encodeAudio(Uploadable $uploadable);

    public function getOutputProgress($id);

    public function getOutputDetails($id);

    public function getJobProgress($id);

    public function getJobDetails($id);

    public function cancelJob($id);

}
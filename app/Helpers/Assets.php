<?php namespace MotionArray\Helpers;

use File;

class Assets {

    public static function addVersion($filename)
    {
        $lastModified = File::lastModified(public_path($filename));

        return $filename . '?v=' . $lastModified;
    }
}
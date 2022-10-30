<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * This model serves as a lookup between slugs returned by the ffmpeg encoder and slugs we use internally for
 * Compressions, FPSs, and Resolutions
 */
class FfmpegSlug extends Model
{
    /**
     * Polymorphic relationship to be used by Compression, Fps, and Resolution models
     */
    public function ffmpegSluggable()
    {
        return $this->morphTo();
    }
}

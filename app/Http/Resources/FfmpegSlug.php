<?php

namespace MotionArray\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FfmpegSlug extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'slug' => $this->slug
        ];
    }
}

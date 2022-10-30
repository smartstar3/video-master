<?php

namespace MotionArray\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AutoDescription extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'data' => $this->data
        ];
    }
}

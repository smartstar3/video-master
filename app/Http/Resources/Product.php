<?php

namespace MotionArray\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Product extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'package_file_path' => $this->package_file_path,
            'description' => $this->description,
            'is_kick_ass' => $this->is_kick_ass,
            'is_requested' => $this->is_requested,
            'preview' => $this->present()->preview("minimal", "low"),
            'category' => new Category($this->category),
        ];
    }
}

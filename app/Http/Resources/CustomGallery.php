<?php

namespace MotionArray\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomGallery extends JsonResource
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
            'slug' => $this->slug,
            'title_text' => $this->title_text,
            'sub_title_text' => $this->sub_title_text,
            'call_to_action_button_text' => $this->call_to_action_button_text,
            'call_to_action_button_href' => $this->call_to_action_button_href,
            'see_more_href' => $this->see_more_href,
            'css' => $this->css,
            'products' => Product::collection($this->collection->products),
        ];
    }
}

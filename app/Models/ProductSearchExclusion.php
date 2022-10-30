<?php

namespace MotionArray\Models;

class ProductSearchExclusion extends BaseModel
{
    public function product()
    {
        return $this->belongsTo('MotionArray\Models\Product');
    }
}

ProductSearchExclusion::created(function($productSearchExclusion){
    $algolia = app()->make('MotionArray\Services\Algolia\AlgoliaClient');

    $algolia->removeProduct($productSearchExclusion->product_id);
});

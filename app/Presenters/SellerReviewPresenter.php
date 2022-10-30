<?php namespace MotionArray\Presenters;

use Config;

class SellerReviewPresenter extends Presenter
{
    public function review()
    {
        return [
            'reviewerName' => $this->entity->reviewer->name_and_last_initial,
            'stars' => $this->entity->stars,
            'review' => $this->entity->review,
            'product' => $this->entity->product->name ?? null,
            'productId' => $this->entity->product->id ?? null,
            'date' => $this->entity->created_at->format('Y-m-d')
        ];
    }
}

<?php namespace MotionArray\Presenters;

use MotionArray\Models\Product;
use Config;
use Auth;

class CollectionPresenter extends Presenter
{

    public function title()
    {
        if ($this->entity->products->count() > 0) return "<a href=\"/account/collections/{$this->entity->slug}\">{$this->entity->title}</a>";
        else return "{$this->entity->title}";
    }

    public function url()
    {
        if ($this->entity->products->count() > 0) return "/account/collections/{$this->entity->slug}";
        else return null;
    }

    public function preview($theme = "standard", $quality = "high", $url = null)
    {
        $product = $this->entity->products->first();
        if ($product) {
            return $product->present()->preview($theme, $quality, $url);
        } else {
            return '<img alt="No products in collection" style="display: block;" src="/assets/images/site/thumb_placeholder.png">';
        }
    }

    public function share()
    {
        /**
         * Restriction checks
         */
        if (!Auth::check() || Auth::user()->disabled) {
            return false;
        }

        if ($this->entity->products->count() > 0) {
            return '<button class="js-collection-share collection__share btn btn--white" data-slug="' . $this->entity->slug . '">
            <span data-toggle="tooltip" title="Share" class="icon--share icon"></span>
            </button>';
        }
    }

    public function delete()
    {

        /**
         * Restriction checks
         */
        if (!Auth::check() || Auth::user()->disabled) {
            return false;
        }

        return '<button class="js-collection-delete collection__delete btn btn--white" data-slug="' . $this->entity->slug . '">
        <span data-toggle="tooltip" title="Delete" class="icon--delete2 icon"></span>
        </button>';
    }


}

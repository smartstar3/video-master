<?php namespace MotionArray\Presenters;

use Config;

class PlanPresenter extends Presenter
{
    public function price($coupon = null, $show_original = false)
    {
        $price = $this->formatPrice($this->entity->price);

        if ($coupon) {
            if ($coupon["percent_off"]) {
                $discounted_price = $this->entity->price - ($this->entity->price * ($coupon["percent_off"] / 100));

                if ($show_original) {
                    $price = $this->formatPrice($discounted_price, true) . " <del>" . $this->formatPrice($this->entity->price) . "</del>";
                }

                $price = $this->formatPrice($discounted_price, true);
            } else {
                $discounted_price = $this->entity->price - $coupon["amount_off"];

                if ($show_original) {
                    $price = $this->formatPrice($discounted_price, true) . " <del>" . $this->formatPrice($this->entity->price) . "</del>";
                }

                $price = $this->formatPrice($discounted_price, true);
            }
        }

        return $price;
    }

    public function discount()
    {
        return Config::get("settings.yearly_discount") . "% Off (-$" . $this->yearlyDiscount . ")";
    }

    public function couponDiscount($coupon)
    {
        if ($coupon["percent_off"]) {
            $total = $this->entity->price - ($this->entity->price * ($coupon["percent_off"] / 100));
            $discount = $this->entity->price - $total;

            return $coupon["percent_off"] . "% Off (-$" . $this->formatPrice($discount, true) . ")";
        } else {
            $discount = $this->entity->price - $coupon["amount_off"];

            return "Fixed Price Off (-$" . $this->formatPrice($discount, true) . ")";
        }
    }

    public function cycle()
    {
        return ucwords($this->entity->cycle);
    }

    public function totalYearlyPrice()
    {
        $total = $this->entity->price / (1 - (Config::get("settings.yearly_discount") / 100));

        return $this->formatPrice($total);
    }

    public function yearlyDiscount()
    {
        $total = $this->entity->price / (1 - (Config::get("settings.yearly_discount") / 100));
        $discount = $total - $this->entity->price;

        return $this->formatPrice($discount);
    }

    public function user_limit()
    {
        return 'Single User';
    }

    public function full_description()
    {
        $plan = $this->entity;

        $formatted_plan = "";

        if ($plan) {
            $period = "";

            $formatted_plan = $plan->name;

            if ($plan->cycle) {
                $formatted_plan .= " (" . $plan->cycle . ") plan";

                if ($plan->cycle == "monthly") {
                    $period = "mo";
                }

                if ($plan->cycle == "yearly") {
                    $period = "year";
                }
            }

            if ($plan->price) {
                $formatted_plan .= " @ $" . $this->price() . " /" . $period;
            }
        }

        return $formatted_plan;
    }

}
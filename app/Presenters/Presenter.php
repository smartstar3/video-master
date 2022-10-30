<?php namespace MotionArray\Presenters;

abstract class Presenter
{
    protected $entity;

    public $defaultDateFormat = "F d, Y";

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function __get($property)
    {
        if (method_exists($this, $property)) {
            return $this->{$property}();
        }

        return $this->entity->{$property};
    }

    /**
     * Price formatting
     *
     * Note: Output for decimal prices is rounded up, remove ceil for
     * accurate output.
     */
    public function formatPrice($price, $force_decimal = null)
    {
        $formatted_price = number_format((float)$price / 100, 2, '.', '');

        if ($force_decimal) {
            return $formatted_price;
        }

        if (strpos($formatted_price, '.00') !== false) {
            return substr($formatted_price, 0, -3);
        }

        return ceil($formatted_price);
    }
}

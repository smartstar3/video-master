<?php namespace MotionArray\Helpers\Portfolio\PortfolioContent;

class PortfolioContentSection extends PortfolioContentGetter
{
    public function getTitle()
    {
        if (!is_null($this->title)) {
            return $this->title;
        } else {
            return $this->type;
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function isMenuActive()
    {
        if (!is_null($this->menu_active)) {
            return $this->isActive() && $this->menu_active;
        }
    }

    public function isActive()
    {
        if (!is_null($this->active)) {
            return $this->active;
        }
    }

    public function getMenuName()
    {
        if (!is_null($this->menu_title)) {
            return $this->menu_title;
        } else {
            return $this->getTitle();
        }
    }

    public function getSliderValue()
    {
        return $this->getTitle();
    }
}
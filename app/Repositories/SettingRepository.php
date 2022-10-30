<?php namespace MotionArray\Repositories;

use JesusRugama\Craft\CraftHelper;

class SettingRepository
{
    protected $craftHelper;

    public function __construct(CraftHelper $craftHelper)
    {
        $this->craftHelper = $craftHelper;
    }

    function getBySlug($slug, $with=null)
    {
        return $this->craftHelper->getGlobal($slug, $with);
    }

    function getConfig($name)
    {
        return $this->craftHelper->getConfigValue($name);
    }
}

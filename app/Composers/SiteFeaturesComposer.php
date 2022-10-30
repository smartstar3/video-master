<?php namespace MotionArray\Composers;

use MotionArray\Repositories\SettingRepository;

class SiteFeaturesComposer
{
    protected $setting;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->setting = $settingRepository;
    }

    public function compose($view)
    {
        $siteFeatures = $this->setting->getBySlug('siteFeatures');

        $view->with('siteFeatures', $siteFeatures->siteFeatures);
    }

}

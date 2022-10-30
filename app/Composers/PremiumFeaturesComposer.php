<?php namespace MotionArray\Composers;

use MotionArray\Repositories\SettingRepository;

class PremiumFeaturesComposer
{
    protected $setting;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->setting = $settingRepository;
    }

    public function compose($view)
    {
        $premiumFeatures = $this->setting->getBySlug('premiumFeatures');

        if(isset($premiumFeatures->premiumFeatures)) {
            $view->with('premiumFeatures', $premiumFeatures->premiumFeatures);
        }
    }

}

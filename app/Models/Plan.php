<?php namespace MotionArray\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use MotionArray\Helpers\Helpers;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Traits\PresentableTrait;

class Plan extends BaseModel
{
    use PresentableTrait;

    protected $presenter = 'MotionArray\Presenters\PlanPresenter';

    protected $guarded = [];

    public static $rules = [];

    public static $updateRules = [];

    public static $messages = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function users()
    {
        return $this->hasMany('MotionArray\Models\User');
    }

    /*
	|--------------------------------------------------------------------------
	| Accesors & Mutators
	|--------------------------------------------------------------------------
	*/
    public function getFeaturesAttribute()
    {
        return $this->getCraftPlanFeatures();
    }

    public function getShortFeaturesListAttribute()
    {
        return $this->getCraftPlanFeatures(true);
    }

    private function getCraftPlanFeatures($useShortList = false)
    {
        $settingRepository = App::make('MotionArray\Repositories\SettingRepository');

        $featureArr = [];

        $featuresSlug = $this->isFree() ? 'freePlans' : 'paymentPlans';

        $planDescription = $settingRepository->getBySlug($featuresSlug);

        if ($useShortList) {
            return $this->renderFeature($planDescription->planIncludes);
        }

        foreach ($planDescription->planFeatures->all() as $feature) {
            $url = null;

            if ($feature->linkTo && $feature->linkTo != 'none') {
                $url = '/' . $feature->linkTo;
            }

            $featureArr[] = [
                'groupTitle' => $this->renderFeature($feature->groupTitle),
                'description' => $this->renderFeature($feature->description),
                'url' => $url
            ];
        }

        return $featureArr;
    }

    /**
     * Replaces predefined variables in the craft fetures
     * (storage, period and per-period)
     *
     * @param $text
     *
     * @return mixed
     */
    private function renderFeature($text)
    {
        $find = ['[storage]', '[period]', '[per-period]'];

        $cycle = $this->isMonthly() ? 'month' : 'year';

        $replace = [
            $this->disk_space,
            $cycle,
            '/ per ' . $cycle
        ];

        foreach ($replace as $i => &$replaceValue) {
            $name = str_replace(['[', ']'], '', $find[$i]);
            $replaceValue = '<span class="replaced-value replaced-value__' . $name . '">' .
                $replaceValue .
                '</span>';
        }

        return str_replace($find, $replace, $text);
    }

    /*
	|--------------------------------------------------------------------------
	| Scopes
	|--------------------------------------------------------------------------
	*/
    public function scopeActive($query)
    {
        $query->where('active', '=', 1);
    }

    /*
	|--------------------------------------------------------------------------
	| Functions
	|--------------------------------------------------------------------------
	*/
    public function diskSpaceInKb()
    {
        return Helpers::gbToKb($this->disk_space);
    }

    public function isFree()
    {
        return $this->billing_id == 'free';
    }

    public function isPro()
    {
        return $this->class == 'pro';
    }

    public function isUnlimited()
    {
        return $this->class == 'unlimited';
    }

    public function isYearly()
    {
        return ($this->cycle === Plans::CYCLE_YEARLY);
    }

    public function isMonthly()
    {
        return ($this->cycle === Plans::CYCLE_MONTHLY);
    }

    public function monthsInCycle()
    {
        if ($this->isYearly()) {
            return 12;
        }

        if ($this->cycle === Plans::CYCLE_6MONTHLY) {
            return 6;
        }

        return 1;
    }
}

<?php namespace MotionArray\Services\UserPlan;

use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Repositories\SettingRepository;

class UserPlanService
{
    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(
        ProductRepository $productRepository,
        SettingRepository $settingRepository
    )
    {
        $this->settingRepository = $settingRepository;
        $this->productRepository = $productRepository;
    }

    public function getFeaturesTooltips()
    {
        $featureKeysFromCraft = [
            'marketplace',
            'portfolio',
            'review',
            'plugins',
            'storage'
        ];

        $featureTooltips = [];

        $tooltipsFromCraft = $this->settingRepository->getBySlug('featureTooltips');
        $productTotalNum = $this->productRepository->getProductsCount();
        $productLast30 = $this->productRepository->getProductsCountCreatedInLast(30);

        if ($tooltipsFromCraft) {
            foreach ($tooltipsFromCraft->featureTooltips as $tooltip) {
                $featureTooltips[$tooltip->feature->value]["description1"] = $tooltip->description1;
                $featureTooltips[$tooltip->feature->value]["description2"] = $tooltip->description2;
            }
        } else {
            foreach ($featureKeysFromCraft as $feature) {
                $featureTooltips[$feature]["description1"] = 'Not defined on Craft';
                $featureTooltips[$feature]["description2"] = 'Not defined on Craft';
            }
        }

        if ($featureTooltips["marketplace"]) {
            $featureTooltips['marketplace']['description1'] = str_replace('#count#', '<span class="tooltip__spec">' . $productTotalNum . '+</span>', $featureTooltips['marketplace']['description1']);
            $featureTooltips['marketplace']['description2'] = str_replace('#count#', '<span class="tooltip__spec">' . $productLast30 . '+</span>', $featureTooltips['marketplace']['description2']);
        }

        return $featureTooltips;
    }
}

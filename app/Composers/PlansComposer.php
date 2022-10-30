<?php namespace MotionArray\Composers;

use Flash;
use Money\Converter;
use Money\Currency;
use Money\Money;
use Request;
use MotionArray\Models\Plan;
use Stripe_Coupon;
use MotionArray\Services\UserPlan\UserPlanService;
use MotionArray\Repositories\PlanRepository;
use Stripe_Error;

class PlansComposer
{
    /**
     * @var PlanRepository
     */
    private $planRepository;

    /**
     * @var UserPlanService
     */
    private $userPlanService;

    /**
     * @var Converter
     */
    private $converter;

    public function __construct(
        UserPlanService $userPlanService,
        PlanRepository $planRepository,
        Converter $converter)
    {
        $this->planRepository = $planRepository;
        $this->userPlanService = $userPlanService;
        $this->converter = $converter;
    }

    public function compose($view)
    {
        $currencyCode = strtoupper(\Request::get('currency', 'USD'));

        $currencySymbol = $this->getCurrencySymbol($currencyCode);
        $showCurrencyDescription = $currencySymbol !== '$';

        $plans = $this->getPlans($currencyCode);

        $code = Request::get('discount');
        $coupon = null;

        if ($code) {
            try {
                if ($coupon = Stripe_Coupon::retrieve($code, config('services.stripe.secret'))->__toArray()) {
                    $discount = $coupon['percent_off'] ? $coupon['percent_off'] . '%' : '$' . ($coupon['amount_off'] / 100);
                    Flash::info('Great! Your ' . $discount . ' discount using <strong>' . $code . '</strong> has been applied.', 'locked');
                }
            } catch (Stripe_Error $e) {
            }
        }

        $featureTooltips = $this->userPlanService->getFeaturesTooltips();

        $view->with(compact(
            'plans',
            'coupon',
            'featureTooltips',
            'currencySymbol',
            'showCurrencyDescription'
        ));
    }

    private function getPlans(?string $currencyCode)
    {
        $plans = Plan::active()->orderBy('order')->get();

        $plans = $plans->map(function (Plan $plan) use ($currencyCode) {
            $attributes = array_only($plan->toArray(), [
                'id',
                'name',
                'cycle',
                'billing_id',
                'disk_space',
            ]);

            $price = Money::USD($plan->price);
            if ($plan->price > 0) {

                if ($currencyCode === 'GBP') {
                    $price = $this->converter->convert($price, new Currency($currencyCode));

                    // When we convert currency, it has cents. Like:
                    // $29 => £23.63
                    // We want to round price up.
                    // So calculation is:
                    // # 2363 / 100 => 24 (Round Up)
                    // # 24 * 100 = 2400
                    // # Client renders: 24
                    $price = $price->divide(100, Money::ROUND_UP)
                        ->multiply(100);
                }
            }

            $attributes['price'] = $price->getAmount();

            return $attributes;
        });

        return $plans;
    }

    private function getCurrencySymbol(?string $currencyCode)
    {
        $symbolMap = [
            'USD' => '$',
            'GBP' => '£',
        ];

        return $symbolMap[strtoupper($currencyCode)];
    }
}

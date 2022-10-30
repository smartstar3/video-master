<?php namespace MotionArray\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class ViewComposersServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        if (isMotionArrayDomain()) {
            if (app()->environment() == 'testing' || !app()->runningInConsole()) {
                View::composer('*', 'MotionArray\Composers\SiteLayoutComposer');
            }

            View::composer('site._partials.secondary-content', 'MotionArray\Composers\SecondaryContentComposer');

            View::composer('site._partials.site-features', 'MotionArray\Composers\SiteFeaturesComposer');

            View::composer(['layouts._partials.site.nav-browse',
                'layouts._partials.site.nav-secondary.categories',
                'layouts._partials.site.nav-requests',
                'layouts._partials.site.nav-marketplace',
                'site.requests._partials.filters',
                'site.requests._partials.new',
                'site._partials.show-filters',
                'admin.requests._partials.sidebar',

                'admin.requests.index',
                'site.requests.index',

            ], 'MotionArray\Composers\CategoriesComposer');

            View::composer([
                'site.plugins._partials.search-filters',
                'layouts._partials.site.nav-plugins',
                'layouts._partials.site.mobile.nav-plugins'
            ], 'MotionArray\Composers\PluginCategoriesComposer');

            View::composer([
                'site.requests._partials.filters',
                'admin.requests._partials.sidebar'
            ], 'MotionArray\Composers\RequestStatusesComposer');

            View::composer(['site._partials.growth',
                'layouts._partials.site.nav-marketplace',
                'admin.automate-newsletters._partials.new-products-footer',
                'admin.automate-newsletters._partials.weekly-recap-footer'
            ], 'MotionArray\Composers\GrowthComposer');

            View::composer([
                'site._partials.pricing.plan-change-app',
            ], 'MotionArray\Composers\PlansComposer');

            View::composer(['site._partials.sign-up-form',
                'site._partials.free-product-preview'], 'MotionArray\Composers\FreeProductComposer');

            View::composer('site._partials.collection-widget', 'MotionArray\Composers\CollectionsComposer');

            View::composer([
                'site.account.portfolio.uploads',
                'site.account.portfolio.portfolio',
                'site.account.portfolio.reviews'
            ], 'MotionArray\Composers\MyUploadsComposer');

            View::composer([
                'site._partials.premium-features'
            ], 'MotionArray\Composers\PremiumFeaturesComposer');

            View::composer([
                'site._partials.questions'
            ], 'MotionArray\Composers\QuestionsComposer');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

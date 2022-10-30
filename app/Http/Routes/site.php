
<?php

use MotionArray\Models\StaticData\Categories;

require __DIR__ . '/site/category-redirects.php';

// Patterns
Route::pattern('id', '[0-9]+');
Route::pattern('page', '[0-9]+');
Route::pattern('ppp', '[0-9]+');

// Pages
Route::group(['domain' => config('app.host')], function () {
    Route::get('/', 'Site\HomeController@index');
});

Route::group(['domain' => config('app.host')], function () {

    // Feed
    Route::get('feed.xml', 'Site\PagesController@feedXML');
    Route::get('feed.rss', 'Site\PagesController@feed');

    // Sitemap
    Route::get('sitemap.xml', 'Site\SitemapsController@redirect');
    Route::group(['prefix' => 'sitemap'], function () {
        Route::get('index.xml', 'Site\SitemapsController@index');
        Route::get('pages.xml', 'Site\SitemapsController@pages');
        Route::get('plugins.xml', 'Site\SitemapsController@plugins');
        Route::get('marketplace.xml', 'Site\SitemapsController@marketplace');
        Route::get('marketplace/{categoryId}/products.xml', 'Site\SitemapsController@products');
        Route::get('marketplace/{categoryId}/products-{page}.xml', 'Site\SitemapsController@products');
    });
});

Route::group(['prefix' => 'books'], function () {
    Route::get('{slug}', 'Site\BooksController@show');
    Route::get('{id}/download', 'Site\BooksController@download')->name('books.download');
});

// Plugins
Route::group(['prefix' => 'plugins'], function () {
    Route::get('download', 'Site\PluginsController@download');

    Route::get('results', 'Site\PluginsController@results');
    Route::get('{categoryGroupSlug}/results', 'Site\PluginsController@results');
    Route::get('{categoryGroupSlug}/{pluginCategorySlug}/results', 'Site\PluginsController@results');

    Route::get('/', 'Site\PluginsController@index')->name('plugins.index');
    Route::get('{categoryGroupSlug}', 'Site\PluginsController@index');
    Route::get('{categoryGroupSlug}/{pluginCategorySlug}', 'Site\PluginsController@index');
    Route::get('{categoryGroupSlug}/{pluginCategorySlug}/{slug}', 'Site\PluginsController@show');
});

// Content Pages
Route::get('contact', 'Site\ContactController@index');
Route::post('contact', 'Site\ContactController@store');

$slugs = [
    'marketplace',
    'review',
    'features',
    'producer-terms-of-service',
    'become-a-producer',
    'privacy-policy',
    'terms-of-service',
    'upload-rules',
    'pricing-2',
    'unlimited'
];
Route::get('{slug}', 'Site\PagesController@slug')
    ->where('slug', implode('|', $slugs));

Route::get('faq', 'Site\PagesController@faq');
Route::redirect('integrations', 'integrations/adobe');
Route::get('integrations/adobe', 'Site\PagesController@adobePanel')->name('integrations.adobe');
Route::get('terms-of-service', 'Site\PagesController@termsOfService')->name('terms-of-service');

// Pricing / Sale Pricing
Route::group(['prefix' => 'pricing', 'middleware' => 'guest'], function () {
    Route::get('/', 'Site\PagesController@pricing')->name('pricing');
    Route::get('sale/', 'Site\PagesController@pricing');
});

// redirect to pricing
Route::redirect('/sign-up', '/pricing');

//adobe panel
Route::group(['prefix' => 'adobe-panel'], function () {
    Route::group(["prefix" => "account/signed", 'middleware' => ['site.signedUser']], function () {
        Route::get("details/{user}", 'Site\AccountsController@index')->name('signed-details');
        Route::get("upgrade/{user}", 'Site\AccountsController@upgrade')->name('signed-upgrade');
        Route::get("collections/{user}", 'Site\Account\CollectionsController@index')->name('signed-collections');
        Route::get('requests/{user}', 'Site\RequestsController@index')->name('signed-requests');
    });
});

// Portfolio
Route::group(['prefix' => 'portfolio'], function () {
    Route::get('/', 'Site\PortfolioController@index');
    Route::get('example/{themeId}', 'Site\PortfolioController@example');
    Route::get('example/{themeId}/project/{project}', 'Site\PortfolioController@projectExample');
});

// Blog
Route::group(["prefix" => "blog"], function () {
    Route::get('/{page?}', 'Site\BlogController@index')->where('page', 'p[0-9]+');
    Route::get('results/{page?}', 'Site\BlogController@results')->where('page', 'p[0-9]+');
    Route::any('{slug}', 'Site\BlogController@show');
});

// Tutorials
Route::group(["prefix" => "tutorials"], function () {
    Route::get('{path}/results/{page?}', 'Site\TutorialsController@results')->where('page', 'p[0-9]+')->where('path', '(.*)');
    Route::get('results/{page?}', 'Site\TutorialsController@results')->where('page', 'p[0-9]+');

    Route::get('/{page?}', 'Site\TutorialsController@index')->where('page', 'p[0-9]+');
    Route::any('{path}/{lastSegment}', 'Site\TutorialsController@index')->where('path', '(.*)');
    Route::any('{lastSegment}', 'Site\TutorialsController@index');
});

Route::get('requests', 'Site\RequestsController@index');


// Signup
Route::group(["prefix" => "sign-up"], function () {
    Route::get('paypal-handler', 'Site\SignupController@paypalPostBackHandler');
    Route::get('thank-you', 'Site\SignupController@thankYou');
    Route::get('thank-you/paid', 'Site\SignupController@thankYou');
    Route::get('thank-you/producer', 'Site\SignupController@thankYouProducer');

    /**
     * Producer Signup
     */
    Route::post('producer', 'Site\UsersController@storeProducer');
    Route::post('producer-upgrade', 'Site\UsersController@upgradeProducer');

    /**
     * Checkout
     */
    Route::get('checkout', 'Site\SignupController@checkout');
    Route::post('checkout', 'Site\UsersController@storeDefault');
    Route::post('checkout-simple', 'Site\UsersController@storeEmailPassword');
});


// Account area
Route::group(["prefix" => "account"], function () {
    /**
     * Sessions
     */
    Route::get('session/create', 'Site\UsersController@create');
    Route::post('session/store', [
        'middleware' => 'invalidate_cache',
        'uses' => 'Site\UsersController@storeSession'
    ]);
    Route::get('session/check', 'Site\UsersController@checkSession');

    /**
     * Account authentication
     */
    Route::get('login', [
        'middleware' => 'invalidate_cache',
        'uses' => 'Site\UsersController@login'
    ])->name('login');

    Route::get('logout', [
        'middleware' => 'invalidate_cache',
        'uses' => 'Site\UsersController@logout'
    ]);

    Route::get('restore/{userId}/{token}', 'Site\UsersController@restore');

    Route::group(["prefix" => "password"], function () {
        Route::post('remind/{one?}/{two?}/{three?}/{four?}/{five?}', 'Site\SiteRemindersController@postRemind');
        Route::get('remind/{one?}/{two?}/{three?}/{four?}/{five?}', 'Site\SiteRemindersController@getRemind');
        Route::post('reset/{one?}/{two?}/{three?}/{four?}/{five?}', 'Site\SiteRemindersController@postReset')->name('password.reset');
        Route::get('reset/{one?}/{two?}/{three?}/{four?}/{five?}', 'Site\SiteRemindersController@getReset');
    });

    Route::get('confirmation/{userId}/{confirmationCode}', 'Site\UsersController@setConfirmed');

    //@todo move this line back into the block below after upgrade page A/B testing is done.
    // It was just moved up so that it can be publicly accessible.
    Route::get("upgrade", 'Site\AccountsController@upgrade');

    Route::group(["middleware" => ["site.auth", "site.subscription"]], function () {

        /**
         * Account pages
         */
        Route::get("/", 'Site\AccountsController@index');
        Route::get("details", 'Site\AccountsController@index');
        Route::get("billing", 'Site\AccountsController@billing');
        Route::get("subscription", 'Site\AccountsController@subscription');
        Route::get("subscription/downgrade", 'Site\AccountsController@downgrade');
        Route::get("invoices", 'Site\AccountsController@invoices');
        Route::get("downloads", 'Site\AccountsController@downloads');
        Route::get("seller-stats", 'Site\AccountsController@sellerStats');
        Route::get("seller-details", 'Site\AccountsController@sellerDetails');
        Route::post('seller-stats', 'Site\AccountsController@postSellerStats');

        Route::group(['prefix' => 'uploads'], function () {
            Route::get("/", 'Site\AccountUploadsController@uploads');

            Route::get("review", 'Site\AccountUploadsController@review');
            Route::get("review/redirect", 'Site\AccountUploadsController@reviewRedirect');

            Route::get("projects/{id}/check-notifications", 'Post\ReviewsController@checkNotifications');
            Route::post("projects/{id}/email-notifications", 'Post\ReviewsController@emailNotifications');

            Route::get("portfolio", 'Site\AccountUploadsController@portfolio');
            Route::get("portfolio/redirect", 'Site\AccountUploadsController@portfolioRedirect');
        });

        Route::group(["prefix" => "collections"], function () {

            Route::get("/", 'Site\Account\CollectionsController@index');

            Route::get("{slug}", [
                'as' => 'account.collections.show',
                'uses' => 'Site\Account\CollectionsController@show'
            ]);

            Route::post('create', [
                'middleware' => 'invalidate_cache',
                'as' => 'collections.create',
                'uses' => 'Site\CollectionsController@create'
            ]);

            Route::post('rename', [
                'as' => 'collections.rename',
                'uses' => 'Site\CollectionsController@rename'
            ]);

            Route::post('remove', [
                'as' => 'collections.remove',
                'uses' => 'Site\CollectionsController@remove'
            ]);

            Route::post('delete', [
                'middleware' => 'invalidate_cache',
                'as' => 'collections.delete',
                'uses' => 'Site\CollectionsController@delete'
            ]);

            Route::post('share', [
                'as' => 'collections.share',
                'uses' => 'Site\CollectionsController@share'
            ]);
        });

        /**
         * Payoneer
         */
        Route::get('payoneer', 'Site\PayoneerController@create');
        Route::delete('payoneer', 'Site\PayoneerController@destroy');

        /**
         * Download routes
         */
        Route::get('download/{id}', 'Site\DownloadsController@download');

        /**
         * Model releases
         */
        Route::get('model-release/{id}', 'Site\ModelReleasesController@download');
        Route::delete('model-release/{id}', 'Site\ModelReleasesController@delete');

        /**
         * User modification actions
         */
        Route::put('{id}', 'Site\UsersController@update');
        Route::put('seller/{id}', 'Site\UsersController@updateSeller');
        Route::put("billing", 'Site\UsersController@updateCard');
        Route::get("subscription/cancel-downgrade", 'Site\UsersController@cancelDowngrade');
        Route::get("subscription/downgrade-now", 'Site\UsersController@downgradeNow');
        Route::post("subscription/cancel", 'Site\UsersController@cancelSubscription');
        Route::post("subscription/schedule-cancel", 'Site\UsersController@scheduleCancel');
        Route::get("subscription/resume", 'Site\UsersController@resumeSubscription');
        Route::post("subscription/change", 'Site\UsersController@changeSubscription');
        Route::post("subscription/create", 'Site\UsersController@createSubscription');
        Route::post("subscription/generate-paypal-url", 'Site\UsersController@generatePaypalUrlAction');
        Route::get("invoices/{invoice_id}", 'Site\UsersController@downloadInvoice');
        Route::delete('delete', 'Site\UsersController@delete');

        /**
         * Submissions
         */
        Route::group(["middleware" => "site.isSeller"], function () {
            Route::get("submissions", 'Site\SubmissionsController@index');
            Route::get("submissions-pending-info", 'Site\SubmissionsController@pendingInfo');
            Route::post('submissions', 'Shared\SubmissionsController@store');
        });

        Route::group(["middleware" => "site.isSubmissionOwner"], function () {
            Route::delete('submissions/{id}', 'Shared\SubmissionsController@destroy');
            Route::put('submissions/{id}/submit', 'Shared\SubmissionsController@submit');
        });

        Route::group(["middleware" => "site.isProductOwner"], function () {
            Route::get('products/{id}', 'Shared\ProductsController@show');
            Route::patch('products/{id}', 'Shared\SubmissionsController@update');
            Route::put('products/{id}', 'Shared\SubmissionsController@update');
        });

        Route::post('projects', 'Shared\ProjectsController@store');
        Route::group(["middleware" => "site.isProjectOwner"], function () {
            Route::delete('projects/{id}', 'Shared\ProjectsController@destroy');
            Route::patch('projects/{id}', 'Shared\ProjectsController@update');
            Route::put('projects/{id}', 'Shared\ProjectsController@update');
        });
    });
});

Route::group(["prefix" => "browse"], function () {

    /**
     * Public preview downloads
     */
    Route::get('download/preview/{id}', 'Site\DownloadsController@downloadPreview');

    /**
     * Search pages
     */
    Route::get('search/no-results', ['as' => 'no_results', 'uses' => 'Site\BrowseController@noResults']);
    Route::get('search/results', 'Site\BrowseController@results');

    /**
     * Browse pages
     */
    Route::get('collection/{slug}', [
        'as' => "collections.public",
        'uses' => 'Site\CollectionsController@index'
    ]);

    Route::get('custom-gallery/{slug}', 'Site\BrowseController@customGallery')->name('gallery.custom');;
    Route::get('/', 'Site\BrowseController@index');
    Route::get('free', 'Site\BrowseController@free');
    Route::get('producer/{seller}', 'Site\BrowseController@seller');
    Route::post('producer/{seller}', 'Site\BrowseController@postSellerForm');
    Route::get('{category}', 'Site\BrowseController@category');
    Route::get('{category}/{subCategory}', 'Site\BrowseController@subCategory');

});


// Product details page
require __DIR__ . '/site/product-redirects.php';
Route::get('{category}/{product}', [
    'as' => 'products.details',
    'uses' => 'Site\BrowseController@product'
])->where('category', implode('|', (new Categories)->dataCollection()->pluck('slug')->toArray()));

// Automated tests
// Shows PHPUnit's report HTML files
if (config('app.env') != 'production') {
    Route::get('/tests/{slug?}', 'Site\TestsController@index')->where('slug', '(.*)');
}

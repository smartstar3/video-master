<?php
/**
 * Sessions
 */
Route::get('mabackend/session/create', 'Admin\UsersController@create');
Route::post('mabackend/session/store', 'Admin\UsersController@storeSession');

/**
 * Admin authentication
 */
Route::get('mabackend/login', 'Admin\UsersController@login');
Route::get('mabackend/logout', 'Admin\UsersController@logout');

//Route::controller('mabackend/password', 'Admin\AdminRemindersController');

Route::get('mabackend/confirmation/{confirmation_code}', 'Admin\UsersController@setConfirmed');

Route::pattern('id', '[0-9]+');

Route::group(['prefix' => 'mabackend', "middleware" => "admin.auth"], function () {

    Route::get('/', 'Admin\DashboardController@index');

    Route::get('change-log', 'Admin\DashboardController@changeLog');

    Route::get('requests', 'Admin\RequestsController@index');

    Route::get('categories', 'Admin\CategoriesController@index');
    Route::get('categories/{id}', 'Admin\CategoriesController@show');
    Route::get('categories/create', 'Admin\CategoriesController@create');
    Route::get('categories/edit/{id}', 'Admin\CategoriesController@edit');
    Route::post('categories', 'Admin\CategoriesController@store');
    Route::put('categories/{id}', 'Admin\CategoriesController@update');
    Route::delete('categories/{id}', 'Admin\CategoriesController@destroy');

    Route::get('sub-categories', 'Admin\SubCategoriesController@index');
    Route::post('sub-categories', 'Admin\SubCategoriesController@store');
    Route::put('sub-categories/{id}', 'Admin\SubCategoriesController@update');
    Route::delete('sub-categories/{id}', 'Admin\SubCategoriesController@destroy');

    Route::get('compressions', 'Admin\CompressionsController@index');
    Route::post('compressions', 'Admin\CompressionsController@store');
    Route::put('compressions/{id}', 'Admin\CompressionsController@update');
    Route::delete('compressions/{id}', 'Admin\CompressionsController@destroy');

    Route::get('resolutions', 'Admin\ResolutionsController@index');
    Route::post('resolutions', 'Admin\ResolutionsController@store');
    Route::put('resolutions/{id}', 'Admin\ResolutionsController@update');
    Route::delete('resolutions/{id}', 'Admin\ResolutionsController@destroy');

    Route::get('versions', 'Admin\VersionsController@index');
    Route::post('versions', 'Admin\VersionsController@store');
    Route::put('versions/{id}', 'Admin\VersionsController@update');
    Route::delete('versions/{id}', 'Admin\VersionsController@destroy');

    Route::get('formats', 'Admin\FormatsController@index');
    Route::post('formats', 'Admin\FormatsController@store');
    Route::put('formats/{id}', 'Admin\FormatsController@update');
    Route::delete('formats/{id}', 'Admin\FormatsController@destroy');

    Route::get('fpss', 'Admin\FpsController@index');
    Route::post('fpss', 'Admin\FpsController@store');
    Route::put('fpss/{id}', 'Admin\FpsController@update');
    Route::delete('fpss/{id}', 'Admin\FpsController@destroy');

    Route::get('sample-rates', 'Admin\SampleRatesController@index');
    Route::post('sample-rates', 'Admin\SampleRatesController@store');
    Route::put('sample-rates/{id}', 'Admin\SampleRatesController@update');
    Route::delete('sample-rates/{id}', 'Admin\SampleRatesController@destroy');

    Route::get('bpms', 'Admin\BpmController@index');
    Route::post('bpms', 'Admin\BpmController@store');
    Route::put('bpms/{id}', 'Admin\BpmController@update');
    Route::delete('bpms/{id}', 'Admin\BpmController@destroy');

    Route::get('product-plugins', 'Admin\ProductPluginsController@index');
    Route::post('product-plugins', 'Admin\ProductPluginsController@store');
    Route::put('product-plugins/{id}', 'Admin\ProductPluginsController@update');
    Route::delete('product-plugins/{id}', 'Admin\ProductPluginsController@destroy');

    Route::get('products/{id}', 'Admin\ProductsController@show');
    Route::get('products/{id}/download', 'Admin\ProductsController@downloadProductForReview');
    Route::post('products', 'Admin\ProductsController@store');
    Route::patch('products/{id}', 'Admin\ProductsController@update');
    Route::put('products/{id}', [
        'as' => 'admin.products.update',
        'uses' => 'Admin\ProductsController@update'
    ]);
    Route::delete('products/{id}', 'Admin\ProductsController@destroy');

    Route::get('products/create', 'Admin\ProductsController@create');
    Route::get('products/edit/{id}', 'Admin\ProductsController@edit');

    /**
     * Products
     */
    Route::get('products', 'Admin\ProductsController@index');
    Route::get('products/{id}/edit', 'Admin\ProductsController@index');

    /**
     * Submissions
     */
    Route::get('submissions', 'Admin\SubmissionsController@index');
    Route::get('submissions/getOldAudio', 'Admin\SubmissionsController@getOldAudio');
    Route::put('submissions/{id}/update/status', 'Admin\SubmissionsController@updateStatus');
    Route::put('submissions/{id}/assign-reviewer', 'Admin\SubmissionsController@assignReviewer');
    Route::put('submissions/{id}/remove-reviewer', 'Admin\SubmissionsController@removeReviewer');
    Route::put('submissions/{id}/send-to-social', 'Admin\SubmissionsController@sendToSocial');
    Route::delete('submissions/{id}', 'Admin\SubmissionsController@destroy');

    Route::get('encoding-errors', 'Admin\DashboardController@encodingErrors');
    Route::get('fix-encoding-errors/{id}', 'Admin\DashboardController@fixEncodingErrors');
    Route::get('create-waveform', 'Admin\DashboardController@createWaveform');
    Route::get('site-settings', 'Admin\SiteSettingsController@siteSettings');
    Route::post('update-site-settings', 'Admin\SiteSettingsController@updateSiteSettings');

    /**
     * Automate Newsletters
     */
    Route::get('automate-newsletters', 'Admin\DashboardController@automateNewsletters');
    Route::get('automate-newsletters/weekly-recap', 'Admin\DashboardController@weeklyRecap');
    Route::get('automate-newsletters/new-products', 'Admin\ProductsController@weeklyProducts');

    /**
     * Auto Descriptions
     */
    Route::get('auto-descriptions', 'Admin\DashboardController@autoDescriptions');

    /**
     * Stats
     */
    Route::get('stats/plugins', 'Admin\StatsController@plugins');
    Route::get('stats/portfolios', 'Admin\StatsController@portfolios');
    Route::get('stats/reviews', 'Admin\StatsController@reviews');

    Route::group(['prefix' => 'earnings'], function () {
        Route::get('/', 'Admin\EarningsController@index');
    });

    /**
     * User Manager
     */
    Route::group(['prefix' => 'user-manager'], function () {
        Route::get('/', 'Admin\UserManagerController@index');
        Route::get('csv', 'Admin\UserManagerController@getCSV');
        Route::get('nopayment-csv', 'Admin\UserManagerController@getNoPaymentCSV');

        Route::get('payout-csv', 'Admin\UserManagerController@getPayoutCSV');
        Route::get('create', 'Admin\UserManagerController@create');
        Route::post('store', 'Admin\UserManagerController@store');
        Route::get('search/results', 'Admin\UserManagerController@searchResults');
        Route::get('{id}/download-history', 'Admin\UserManagerController@getDownloadHistory');
        Route::get('{id}/edit', 'Admin\UserManagerController@edit');
        Route::put('{id}/edit', 'Admin\UserManagerController@updateDetails');
        Route::put('{id}/toggle-status', 'Admin\UserManagerController@toggleStatus');
        Route::put('{id}/toggle-force-log-out', 'Admin\UserManagerController@toggleForceLogOut');
        Route::get('{id}/reset-password', 'Admin\UserManagerController@resetPassword');
        Route::put('{id}/reset-password', 'Admin\UserManagerController@updatePassword');
        Route::get('{id}/change-role', 'Admin\UserManagerController@changeRole');
        Route::put('{id}/change-role', 'Admin\UserManagerController@updateRole');
        Route::get('{id}/confirm-delete', 'Admin\UserManagerController@confirmDelete');
        Route::put('{id}/delete', 'Admin\UserManagerController@delete');
        Route::get('{id}/confirm-downgrade', 'Admin\UserSubscriptionController@confirmDowngrade');
        Route::put('{id}/downgrade', 'Admin\UserSubscriptionController@downgrade');
        Route::get('{id}/confirm-change-subscription-to-monthly', 'Admin\UserSubscriptionController@confirmChangeSubscriptionToMonthly');
        Route::put('{id}/change-subscription-to-monthly', 'Admin\UserSubscriptionController@changeSubscriptionToMonthly');
        Route::get('{id}/setup-freeloader', 'Admin\UserManagerController@freeloader');
        Route::put('{id}/setup-freeloader', 'Admin\UserManagerController@updateFreeloader');
        Route::get('{id}/confirm-revoke-freeloader', 'Admin\UserManagerController@confirmRevokeFreeloader');
        Route::get('{id}/login', 'Admin\UserManagerController@logInAs');
    });
});

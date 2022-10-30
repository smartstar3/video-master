<?php

/**
 * Stripe
 */
Route::post('stripe/webhook', '\MotionArray\Http\Controllers\Webhooks\StripeWebhookController@handleWebhook');

/**
 * Paypal
 */
Route::post('paypal/webhook', '\MotionArray\Http\Controllers\Webhooks\PaypalWebhookController@handleWebhook');

/**
 * Payoneer
 */
Route::get('payoneer/webhook', '\MotionArray\Http\Controllers\Webhooks\PayoneerController@handleWebhook');

/**
 * Zencoder
 */
Route::post('zencoder/webhook', '\MotionArray\Http\Controllers\Webhooks\ZencoderController@webhook');

/**
 * MA Plugins
 */
Route::post('api/plugins/auth', 'API\PluginsController@auth');
Route::post('api/plugins/check', 'API\PluginsController@check');

Route::get('api/user-sites', 'API\UserSitesController@index');

Route::get('health', 'Site\PagesController@health');


Route::group(["prefix" => "api/v2"], function () {

    /**
     * Auth
     */
    Route::post('auth', 'API\v2\AuthController@auth');

    Route::get('compressions', 'API\v2\CompressionsController@index');
    Route::get('fpss', 'API\v2\FpssController@index');
    Route::get('resolutions', 'API\v2\ResolutionsController@index');

    Route::group(['middleware' => 'auth:api'], function () {
        /**
         * Me
         */
        Route::get('me', 'API\v2\MeController@show');

        /**
         * Products
         */
        Route::post('products', 'API\v2\ProductsController@store');
        Route::put('products/{id}', 'API\v2\ProductsController@update');
        Route::post('products/batch', 'API\v2\ProductsController@batchStore');
        Route::get('products/{id}/encoding/progress/', 'API\v2\ProductsController@getEncodingProgress');
    });
});

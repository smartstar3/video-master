<?php

/**
 * Preview Theme
 */
Route::group(['namespace' => 'Post', 'prefix' => 'portfolio/theme-preview/{id}'], function () {
    Route::get('/', [
        'as' => 'portfolio.preview-theme.index',
        'uses' => 'PortfoliosPreviewController@show'
    ]);

    Route::get('/project/{slug}', [
        'as' => 'portfolio.preview-theme.project',
        'uses' => 'PortfoliosPreviewController@project'
    ]);
});

Route::group(['namespace' => 'Post', 'middleware' => 'userSite'], function () {

    Route::group(['domain' => config('app.host')], function () {
        Route::get('portfolio/insider-preview', [
            'as' => 'portfolio.insider-preview',
            'uses' => 'PortfoliosController@show'
        ]);

        Route::group(['prefix' => 'account/portfolio', "middleware" => ["site.auth", "hasPortfolio"]], function () {

            /**
             * Edit Portfolio
             */
            Route::group(['prefix' => 'edit', "middleware" => ["site.subscription"]], function () {

                Route::get('/', [
                    'as' => 'portfolio.edit.index',
                    'uses' => 'PortfoliosController@show'
                ]);

                Route::post('upload-image', 'PortfoliosController@uploadImage');

                Route::post('copy-image', 'PortfoliosController@copyImage');

                Route::group(['middleware' => 'site.isProjectOwner'], function () {
                    Route::get('/project/{project}', [
                        'as' => 'portfolio.edit.project',
                        'uses' => 'PortfoliosController@project'
                    ]);
                });
            });

            /**
             * Preview unpublished changes
             */
            Route::group(['prefix' => 'preview'], function () {
                Route::get('/', [
                    'as' => 'portfolio.preview.index',
                    'uses' => 'PortfoliosController@show'
                ]);

                Route::get('/project/{slug}', [
                    'as' => 'portfolio.preview.project',
                    'uses' => 'PortfoliosController@project'
                ]);
            });
        });
    });

    Route::group(["middleware" => ["portfolio.isPublished"]], function () {
        Route::get('/', 'PortfoliosController@show');

        Route::get('/project/{slug}', 'PortfoliosController@project');
    });

    Route::group(['prefix' => 'review'], function () {
        Route::get('{permalink}', 'ReviewsController@project');

        Route::get('{permalink}/version/{version}', 'ReviewsController@project');

        Route::get('invitation/{token}/version/{version}', 'ReviewsController@invitation');

        Route::post('{permalink}/unlock', 'ReviewsController@unlock');

        Route::get('{permalink}/unsubscribe/{notificationId}/{key}', 'ReviewsController@unsubscribe');

        Route::get('{permalink}/resubscribe/{notificationId}/{key}', 'ReviewsController@resubscribe');
    });
});

Route::group(['namespace' => 'Post'], function () {
    Route::post('/portfolio/contact', 'PortfoliosController@sendMessage')->name('portfolio.contact');
});

Route::group(['namespace' => 'API'], function () {
    Route::get('/status-check', 'PortfoliosController@statusCheck');
});

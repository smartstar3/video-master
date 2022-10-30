<?php

//@todo
// move this to use the UserController in the API namespace, instead of the cone in the
// Site namespace. Also move controller content into helper class or repository.
Route::post('api/sign-up', 'Site\UsersController@storeDefault');
Route::post('api/sign-up/validate', 'Site\UsersController@storeValidateDefault');
Route::post('api/sign-up-no-name', 'Site\UsersController@storeWithoutName');
Route::post('api/sign-up/validate-no-name', 'Site\UsersController@storeValidateWithoutName');
Route::post('api/sign-up-full-name', 'Site\UsersController@storeFullName');
Route::post('api/sign-up/validate-full-name', 'Site\UsersController@storeFullNameValidate');
Route::post('api/sign-up-email-password', 'Site\UsersController@storeEmailPassword');
Route::post('api/sign-up/validate-email-password', 'Site\UsersController@storeValidateEmailPassword');
Route::post('api/producers/sign-up', 'Site\UsersController@storeProducer');
Route::post('api/producers/sign-up/validate', 'Site\UsersController@storeProducerValidate');
Route::post('api/producers/producer-upgrade', 'Site\UsersController@upgradeProducer');
Route::post('api/sign-in', 'Site\UsersController@storeSession');

Route::group(['prefix' => 'api', 'namespace' => 'API'], function () {

    Route::post('products/search', 'ProductSearchController@search');

    Route::get('download-preview-file/{id}', '\MotionArray\Http\Controllers\Site\DownloadsController@downloadPreviewFile');

    Route::post('books/unlock', 'BooksController@unlock');

    Route::get('upload-rules', 'OutputsController@getUploadRules');

    /**
     * Unsplash
     */
    Route::group(['prefix' => 'unsplash'], function () {
        Route::get('search/{term?}/{page?}', 'UnsplashController@search');
        Route::get('download/{photoId}', 'UnsplashController@download');
    });


    /**
     *   Portfolio Themes
     */
    Route::group(['prefix' => 'portfolio-themes'], function () {
        Route::get('/', 'PortfolioThemesController@index');

        Route::put('{themeId}/activate', 'PortfolioThemesController@activate');
        Route::post('{themeId}/duplicate', 'PortfolioThemesController@duplicate');

        Route::group(['middleware' => 'admin.auth'], function () {
            Route::post('{themeId}/make-site-theme', 'PortfolioThemesController@makeSiteTheme');
        });

        Route::group(['middleware' => 'portfolioThemeOwner'], function () {
            Route::put('{themeId}/rename', 'PortfolioThemesController@rename');

            Route::delete('{themeId}', 'PortfolioThemesController@destroy')->where('themeId', '[0-9]+');;
        });
    });

    /**
     *   Requests
     */
    Route::group(['prefix' => 'requests'], function () {
        Route::get('/', 'RequestsController@index');

        Route::get('{id}', 'RequestsController@show');

        Route::group(["middleware" => ["site.auth", "site.subscription"]], function () {
            Route::post('/', 'RequestsController@create');
            Route::put('{id}/toggle-upvote', 'RequestsController@toggleUpvote');
            Route::put('{id}/upvote', 'RequestsController@upvote');
        });

        Route::group(['middleware' => 'requestOwner'], function () {
            Route::put('{id}', 'RequestsController@update');
            Route::delete('{id}', 'RequestsController@destroy');
        });

        Route::group(['middleware' => "admin.auth"], function () {
            // Approve, Reject
            Route::put('{id}/update/status', 'RequestsController@updateStatus');
        });
    });

    /**
     * Portfolio
     */
    Route::get('user-site', 'UserSitesController@show');
    Route::put('user-sites', 'UserSitesController@update');

    Route::put('review-apps/update-settings', 'ReviewsController@updateSettings');

    Route::get('projects', 'ProjectsController@index');

    Route::post('portfolios/reset', 'PortfoliosController@reset');

    Route::post('{upload_type}/{id}/upload-custom-placeholder', 'PreviewUploadsController@uploadCustomPlaceholder');

    Route::group(['prefix' => 'portfolios/{portfolioId}', 'middleware' => 'portfolioOwner'], function () {
        Route::put('update-content', 'PortfoliosController@updateContent');
        Route::put('update-color-picker', 'PortfoliosController@updateColorPicker');
        Route::put('projects/{projectId}', 'PortfoliosController@updateContent');

        Route::put('publish', 'PortfoliosController@publish');

        Route::put('unpublish', 'PortfoliosController@unpublish');

        Route::get('/uploads', 'PortfolioUploadsController@index');
        Route::get('/uploads/{portfolioUploadId}/download', 'PortfolioUploadsController@download');
        Route::delete('/uploads/{portfolioUploadId}', 'PortfolioUploadsController@delete');

        Route::get('/settings/{path}', 'PortfoliosController@getSettings');
    });

    /**
     * Encoding
     */
    Route::group(['prefix' => 'encoding'], function () {
        Route::get('outputs/{output_id}/progress', 'OutputsController@getOutputProgress');
        Route::get('outputs/{output_id}/details', 'OutputsController@getOutputDetails');
        Route::get('jobs/{job_id}/progress', 'OutputsController@getJobProgress');
        Route::get('jobs/{job_id}/details', 'OutputsController@getJobDetails');
    });

    /**
     * Products
     */
    Route::group(['prefix' => 'products/{id}/encoding/jobs/'], function () {
        Route::get('progress', 'OutputsController@getJobProgressByProduct');
        Route::get('details', 'OutputsController@getJobDetailsByProduct');
        Route::put('cancel', 'OutputsController@cancelJobByProduct');
    });

    Route::group(['prefix' => 'products'], function () {
        Route::get('audio-placeholders/{id?}', 'ProductsController@getAudioPlaceholders');
        Route::get('processing', 'ProductsController@productsByProcessing');
        Route::get('unpublished', 'ProductsController@productsByUnpublished');
        Route::get('search/{query}', 'ProductsController@productsSearchResults');
        Route::get('search/{query}/page/{page_no?}', 'ProductsController@productsSearchResults');
        Route::get('search/{query}/count', 'ProductsController@productsSearchResultsCount');
        Route::get('category/{category_id}/page/{page_no?}', 'ProductsController@productsByCategory');
        Route::get('category/{category_id}/count', 'ProductsController@totalProductsInCategory');
    });

    Route::group(['prefix' => 'plugins'], function () {
        Route::get('/', 'PluginsController@index');
    });

    /**
     * Projects
     */
    Route::group(['prefix' => 'projects/{projectId}/'], function () {
        Route::get('/', 'ProjectsController@show');

//		Route::group(['middleware' => 'site.isProductOwner'], function () {
        Route::post('upload-image', 'ProjectsController@uploadImage');
        Route::delete('remove-image', 'ProjectsController@removeImage');
//		});


        Route::group(['prefix' => 'versions/'], function () {
            Route::get('/', 'PreviewUploadsController@index');
            Route::get('active', 'PreviewUploadsController@active');
            Route::get('{version}', 'PreviewUploadsController@show')->where('version', '[0-9]+');

            Route::get('{version}/authors', 'ProjectCommentAuthorsController@index');
            Route::post('{version}/authors/notify', 'ProjectCommentAuthorsController@notify');
            Route::post('{version}/authors/approve-revision', 'ProjectCommentAuthorsController@approveRevision');

            Route::post('delete-multiple', 'PreviewUploadsController@deleteMultiple');

            Route::group(['prefix' => '{version}/comments/'], function () {
                Route::get('/', 'ProjectCommentsController@index');
                Route::post('/', 'ProjectCommentsController@create');

                Route::put('{commentId}/toggle-checked-state', 'ProjectCommentsController@toggleCheckedState');

                Route::group(["middleware" => ["comments.manage"]], function () {
                    Route::put('{commentId}', 'ProjectCommentsController@update');
                    // JSONP
                    Route::get('{commentId}/delete', 'ProjectCommentsController@destroy');
                });
            });
        });
    });

    Route::group(['prefix' => 'projects/{id}/encoding/jobs/'], function () {
        Route::get('progress', 'OutputsController@getJobProgressByProject');
        Route::get('details', 'OutputsController@getJobDetailsByProject');
        Route::put('cancel', 'OutputsController@cancelJobByProject');
    });

    /**
     * Categories
     */
    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', 'CategoriesController@index');
    });

    /**
     * Collections
     */
    Route::group(['prefix' => 'collections'], function () {
        Route::get('/', 'CollectionsController@index');
        Route::get('/products/', 'CollectionsController@products');

        Route::get('/product-ids', 'CollectionsController@indexWithProductIds');
        Route::post('/', 'CollectionsController@create');
        Route::put('/{collection}', 'CollectionsController@update');
        Route::delete('/{collection}', 'CollectionsController@delete');
        Route::post('/{collection}/add', 'CollectionsController@addProduct');
        Route::post('/{collection}/remove', 'CollectionsController@removeProduct');
    });

    /**
     * User
     */
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', 'UsersController@show');
        Route::get('send-confirmation-email', 'UsersController@sendConfirmationEmail');
        Route::get('renewal-date', 'UsersController@renewalDate');
        Route::post('uploading', 'UsersController@uploading');
        Route::post('validate-plan-change', 'UsersController@validatePlanChange');
        Route::get('sellers-i-follow', 'UsersController@sellersIFollow');
        Route::put('accept-tos', 'UsersController@acceptTos');
    });

    /**
     *  Seller
     */
    Route::group(["prefix" => "sellers"], function () {
        Route::put('{id}', 'SellersController@update');
        Route::put('{id}/follow', 'SellersController@follow');
        Route::put('{id}/unfollow', 'SellersController@unfollow');
        Route::put('{id}/my-review', 'SellersController@updateMyReview');
        Route::put('{id}/profile-image', 'SellersController@updateProfileImage');
        Route::put('{id}/header-image', 'SellersController@updateHeaderImage');
        Route::get('{id}/reviews', 'SellersController@reviewsIndex');
        Route::get('{id}/reviews-summary', 'SellersController@reviewsSummary');
        Route::get('{id}/review-totals', 'SellersController@reviewTotals');
        Route::get('{id}/my-downloads', 'SellersController@myDownloads');
        Route::get('{id}/my-paid-downloads', 'SellersController@myPaidDownloads');
        Route::get('{id}/my-review', 'SellersController@showMyReview');
        Route::get('{id}/am-i-following', 'SellersController@amIFollowing');
        Route::get('{id}/follower-count', 'SellersController@followerCount');
        Route::post('{id}/message', 'SellersController@message');

        Route::group(['middleware' => 'throttle:30,1'], function () {
            Route::get('{sellerId}/stats', 'SellersController@stats');
        });
    });


    Route::group(['middleware' => 'throttle:30,1'], function () {
        Route::get('me/stats', 'SellersController@stats');
        Route::get('site/stats', 'SiteController@stats');
    });

    Route::get('auto-descriptions', 'AutoDescriptionsController@index');
    Route::put('auto-descriptions/{category}/{name}', 'AutoDescriptionsController@update');
    Route::get('auto-descriptions/generate-stock-video-description/{slug}', 'AutoDescriptionsController@generateStockVideoDescription');
});

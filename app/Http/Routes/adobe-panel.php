<?php

Route::group(['prefix' => 'adobe-panel/api', 'namespace' => 'API\v2\AdobePanel'], function () {
    Route::post('auth', 'AuthController@auth');
    Route::get('site-urls', 'UrlController@siteUrls');
    Route::post('products/search', 'ProductsController@search');
    Route::get('products/{id}', 'ProductsController@show');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('me', 'MeController@show');
        Route::get('signed-urls', 'UrlController@signedUrls');
        Route::get('products/{id}/download-url', 'DownloadController@downloadUrl');
        Route::post('products/user-downloads', 'DownloadController@userDownloads');
    });
});

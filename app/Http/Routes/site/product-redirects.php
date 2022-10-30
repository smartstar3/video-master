<?php

use MotionArray\Models\StaticData\Categories;

$productSlugRedirects = [
    '/'.Categories::STOCK_MUSIC.'/ambient-droplets' => 2274,
    '/'.Categories::STOCK_MUSIC.'/chasing-love' => 3424,
    '/after-effects-templates/christmas-intro' => 3341,
    '/'.Categories::STOCK_MUSIC.'/christmas-intro' => 3463,
    '/'.Categories::STOCK_MUSIC.'/inspiring-track' => 2533
];

foreach ($productSlugRedirects as $from => $id) {
    $to = $from . '-' . $id;
    Route::get($from, 'Site\RedirectController@product')->defaults('to', $to);
}

<?php

use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\SubCategories;

$categoryRedirects = [];
foreach (Categories::legacySlugs() as $old => $new) {
    /**
     * motionarray.com/some-category
     */
    $categoryRedirects['/' . $old] = '/' . $new;
    /**
     * motionarray.com/browse/some-category
     */
    $categoryRedirects['/browse/' . $old] = '/browse/' . $new;
}

// redirect category slugs to latest before subcategories
foreach ($categoryRedirects as $from => $to) {
    Route::get($from . '/{part1?}/{part2?}/{part3?}/{part4?}/{part5?}', 'Site\RedirectController@browse')
        ->defaults('to', $to);
}

$subCategoryRedirects = [];
foreach (SubCategories::legacySlugs() as $categorySlug => $redirects) {
    foreach ($redirects as $old => $new) {
        $prefix = '/browse/' . $categorySlug;
        /**
         * motionarray.com/browse/some-category/some-sub-category
         */
        $subCategoryRedirects[$prefix . '/' . $old] = $prefix . '/' . $new;
    }
}

foreach ($subCategoryRedirects as $from => $to) {
    Route::get($from . '/{part1?}/{part2?}/{part3?}/{part4?}/{part5?}', 'Site\RedirectController@browse')
        ->defaults('to', $to);
}

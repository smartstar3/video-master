<?php

namespace MotionArray\Http\Controllers\API;

use MotionArray\Services\Unsplash\UnsplashClient;

/**
 * Class UnsplashController
 *
 * @package MotionArray\Http\Controllers\Site
 */
class UnsplashController extends BaseController
{
    protected $unsplashClient;

    public function __construct(UnsplashClient $unsplashClient)
    {
        $this->unsplashClient = $unsplashClient;
    }

    /**
     * Search for Images
     *
     * @param $term
     * @param $page
     * @return \Illuminate\Http\JsonResponse
     */
    public function search($term, $page)
    {
        $results = $this->unsplashClient->search($term, $page);

        // Add credit link
        $images = array_map(function ($image) {
            $url = $image['user']['links']['html'];

            $image['user']['links']['credit'] = $url .
                '?utm_source=' . config('unsplash.connection.utmSource') .
                '&utm_medium=referral&utm_campaign=api-credit';

            return $image;
        }, $results->getResults());

        return response()->json([
            'results' => $images,
            'totalPages' => $results->getTotalPages(),
            'total' => $results->getTotal(),
            'page' => (int)$page, 'term' => $term
        ]);
    }

    public function download($photoId)
    {
        $response = $this->unsplashClient->download($photoId);

        return response()->json([
            'success' => true,
            'response' => $response
        ]);
    }
}

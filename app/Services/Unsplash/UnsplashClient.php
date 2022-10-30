<?php

namespace MotionArray\Services\Unsplash;

use Crew\Unsplash\HttpClient;
use Crew\Unsplash\Search;
use Crew\Unsplash\Photo;

/**
 * Class UnsplashClient
 *
 * @package MotionArray\Services\Unsplash
 */
class UnsplashClient
{

    /**
     * Results Per Page Variable
     *
     * @var int
     */
    private $results_per_page;

    /**
     * UnsplashClient constructor.
     */
    public function __construct()
    {
        HttpClient::init(config('unsplash.connection'));

        $this->results_per_page = config('unsplash.results_per_page');
    }

    /**
     * Search
     *
     * @param $term
     * @param $page
     * @param null $per_page
     * @return \Crew\Unsplash\PageResult
     */
    public function search($term, $page, $per_page = null)
    {
        if (is_null($per_page)) {
            $per_page = $this->results_per_page;
        }

        return Search::photos($term, $page, $per_page);
    }

    public function download($photoId)
    {
        $photo = Photo::find($photoId);

        return $photo->download();
    }

}

<?php

namespace MotionArray\Services\Algolia;

abstract class AlgoliaResponseParser
{
    /**
     * @var bool
     */
    protected $useImgix;

    public function __construct()
    {
        $this->useImgix = config('imgix.use_imgix');
    }

    abstract public function responseToArray(array $results): array;
}

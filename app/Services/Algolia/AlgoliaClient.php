<?php namespace MotionArray\Services\Algolia;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Index;
use MotionArray\Facades\Algolia;
use Config;

class AlgoliaClient
{
    /**
     * @var AlgoliaSearchRequestAdapter
     */
    protected $adapter;

    /**
     * @var AlgoliaCache
     */
    protected $cache;

    public function __construct(
        AlgoliaSearchRequestAdapter $adapter,
        AlgoliaCache $cache
    )
    {
        $this->adapter = $adapter;
        $this->cache = $cache;
    }

    public function getIndex($index = null): Index
    {
        if (!$index) {
            $index = Config::get('algolia.index');
        }
        $index = Algolia::initIndex($index);

        return $index;
    }

    public function sendProducts(Array $products = [])
    {
        $this->getIndex()->addObjects($products);
    }

    public function updateProduct($objectID, Array $data, $createIfNotExists = true)
    {
        $data['objectID'] = $objectID;

        $this->getIndex()->partialUpdateObject($data, $createIfNotExists);
    }

    /**
     * Removes a single product from the Algolia index
     *
     * @param integer $productId
     * @throws AlgoliaException
     */
    public function removeProduct($productId)
    {
        $this->getIndex()->deleteObject($productId);
    }

    /**
     * Removes multiple products from the Algolia index
     *
     * @param array $productIds an array of products IDs to remove from the Algolia index
     */
    public function removeProducts($productIds)
    {
        $this->getIndex()->deleteObjects($productIds);
    }

    /**
     * @param string|array $searchTags The text string we're doing full text search on.
     * @param $index
     * @param array $options Options documentation: https://www.algolia.com/doc/api-reference/api-methods/search/?language=php
     * @return array
     * @throws AlgoliaException
     */
    public function searchRaw($searchTags, array $options = [], string $index = null): array
    {
        $searchTags = array_wrap($searchTags);
        $searchTags = implode(',', $searchTags);

        /** @var Index $service */
        $service = $this->getIndex($index);
        $results = $service->search($searchTags, $options);
        $results['index'] = $service->indexName;

        return $results;
    }

    public function searchWithRequestCached(AlgoliaSearchRequest $request): array
    {
        return $this->cache->rememberRequest($request, function () use ($request) {
            return $this->searchWithRequest($request);
        });
    }

    public function searchWithRequest(AlgoliaSearchRequest $request): array
    {
        $requestedPerPage = $request->attributes()['perPage'];
        $request = $this->adapter->build($request);
        $index = $request['index'];
        $options = $request['options'];
        $searchTags = $request['searchTags'];

        $results = $this->searchRaw($searchTags, $options, $index);
        $response = $this->prepareResults($results);

        return array_merge($response, [
            'options' => $options,
            'searchTags' => $searchTags,
            'perPage' => $requestedPerPage,
        ]);
    }

    public function search($searchTags, array $options = [], string $index = null): array
    {
        $results = $this->searchRaw($searchTags, $options, $index);
        $response = $this->prepareResults($results);

        return array_merge($response, [
            'options' => $options,
            'searchTags' => $searchTags,
        ]);
    }

    private function prepareResults(array $results): array
    {
        $products = $results['hits'];
        // this value is the total number of results
        // it may be higher than the number accessible by pagination depending on configuration
        // see: https://www.algolia.com/doc/api-reference/api-parameters/paginationLimitedTo/
        // $total = $results['nbHits'];

        $index = $results['index'];
        $lastPage = $results['nbPages'];
        $perPage = $results['hitsPerPage'];
        // total actually accessible via pagination
        $accessibleTotal = $lastPage * $perPage;
        $currentPage = $results['page'] + 1;

        return [
            'index' => $index,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'total' => $accessibleTotal,
            'products' => $products,
        ];
    }
}

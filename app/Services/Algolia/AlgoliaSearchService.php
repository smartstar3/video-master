<?php

namespace MotionArray\Services\Algolia;

use MotionArray\Models\User;
use MotionArray\Repositories\DownloadRepository;

class AlgoliaSearchService
{
    /**
     * @var AlgoliaClient
     */
    private $client;

    /**
     * @var DownloadRepository
     */
    private $downloadRepository;

    public function __construct(AlgoliaClient $client, DownloadRepository $downloadRepository)
    {
        $this->client = $client;
        $this->downloadRepository = $downloadRepository;
    }

    public function searchForAdobePanel(array $attributes, User $loggedInUser = null): array
    {
        $results = $this->search($attributes, $loggedInUser);

        return app(AlgoliaResponseParserForAdobePanel::class)
            ->responseToArray($results);
    }

    public function searchForSite(array $attributes, User $loggedInUser = null): array
    {
        $results = $this->search($attributes, $loggedInUser);

        $parsedResults = app(AlgoliaResponseParserForSite::class)
            ->responseToArray($results);

        $parsedResults['products'] = $this->addIsDownloadedByUserToProducts($parsedResults['products'], $loggedInUser);
        return $parsedResults;
    }

    private function search(array $attributes, User $loggedInUser = null): array
    {
        $algoliaRequest = new AlgoliaSearchRequest($attributes);
        $user = $loggedInUser;
        $hasUserSpecificFilters = $algoliaRequest->hasUserSpecificFilters();
        $shouldCacheRequests = !$hasUserSpecificFilters && !($user && $user->isAdmin());

        if ($shouldCacheRequests) {
            $results = $this->client->searchWithRequestCached($algoliaRequest);
        } else {
            $results = $this->client->searchWithRequest($algoliaRequest);
        }

        return $results;
    }

    /**
     * Add an attribute to indicate whether the user has downloaded the product.
     */
    private function addIsDownloadedByUserToProducts(array $products, User $user = null): array
    {
        if ($user) {
            $productIds = collect($products)->pluck('id')->toArray();
            $downloadedProductIds = $this->downloadRepository->userDownloadedProductIds($user->id, $productIds);
        } else {
            $downloadedProductIds = [];
        }

        return collect($products)
            ->map(function ($item) use ($downloadedProductIds) {
                $productId = $item['id'];
                $item['is_downloaded_by_user'] = in_array($productId, $downloadedProductIds);
                return $item;
            })
            ->toArray();
    }
}

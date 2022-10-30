<?php

namespace MotionArray\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use MotionArray\Services\Algolia\AlgoliaSearchService;
use MotionArray\Services\Algolia\AlgoliaResponseParserForSite;

class ProductSearchController extends Controller
{
    public function search(Request $request, AlgoliaSearchService $service)
    {
        return $service->searchForSite($request->all(), Auth::user());
    }
}

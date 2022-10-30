<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Carbon\Carbon;

class SiteController extends BaseController
{
    public function stats(Request $request)
    {
        if (!auth()->check()) {
            return response('Unauthorized.', 401);
        }

        $month = $request->get("month");

        $year = $request->get("year");

        $dateStart = Carbon::create($year, $month, 1, 0, 0, 0)->startOfMonth();

        $dateEnd = $dateStart->copy()->endOfMonth();

        $statsService = app('MotionArray\Services\SellerStats\SellerStatsService');

        $response = $statsService->siteStats($dateStart, $dateEnd, -1);

        return new JsonResponse($response, 200);
    }
}

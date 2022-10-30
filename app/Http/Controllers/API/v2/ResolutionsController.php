<?php

namespace MotionArray\Http\Controllers\API\v2;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MotionArray\Models\Resolution;
use MotionArray\Http\Resources\FfmpegSlug as FfmpegSlugResource;
use MotionArray\Http\Resources\Resolution as ResolutionResource;

class ResolutionsController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $useEncoderSlugs = (bool)$request->get('useEncoderSlugs');

        $resolutions = Resolution::all();

        if ($useEncoderSlugs) {
            $result = [];

            foreach ($resolutions as $resolution) {
                array_push($result,  new FfmpegSlugResource($resolution->ffmpegSlug));
            }

            return new JsonResponse([
                'data' => $result
            ], 200);
        }

        return ResolutionResource::collection($resolutions);
    }
}

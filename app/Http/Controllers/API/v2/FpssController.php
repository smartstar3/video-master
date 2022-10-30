<?php

namespace MotionArray\Http\Controllers\API\v2;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MotionArray\Models\Fps;
use MotionArray\Http\Resources\Fps as FpsResource;
use MotionArray\Http\Resources\FfmpegSlug as FfmpegSlugResource;

class FpssController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $useEncoderSlugs = (bool)$request->get('useEncoderSlugs');

        $fpss = Fps::all();

        if ($useEncoderSlugs) {
            $result = [];

            foreach ($fpss as $fps) {
                $slugObjArray = FfmpegSlugResource::collection($fps->ffmpegSlugs);

                foreach ($slugObjArray as $slugObj) {
                    array_push($result, $slugObj);
                }
            }

            return new JsonResponse([
                'data' => $result
            ], 200);
        }

        return FpsResource::collection($fpss);
    }
}

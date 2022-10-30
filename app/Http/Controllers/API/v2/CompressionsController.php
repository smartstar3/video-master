<?php

namespace MotionArray\Http\Controllers\API\v2;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MotionArray\Models\Compression;
use MotionArray\Http\Resources\FfmpegSlug as FfmpegSlugResource;
use MotionArray\Http\Resources\Compression as CompressionResource;

class CompressionsController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $useEncoderSlugs = (bool)$request->get('useEncoderSlugs');

        $compressions = Compression::all();

        if($useEncoderSlugs) {
            $result = [];

            foreach ($compressions as $compression) {
                $slugObjArray = FfmpegSlugResource::collection($compression->ffmpegSlugs);

                foreach ($slugObjArray as $slugObj) {
                    array_push($result, $slugObj);
                }
            }

            return new JsonResponse([
                'data' => $result
            ], 200);
        }

        return CompressionResource::collection($compressions);
    }
}

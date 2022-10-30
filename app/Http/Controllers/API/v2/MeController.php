<?php

namespace MotionArray\Http\Controllers\API\v2;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class MeController extends Controller
{
    /**
     * Get the logged in user's info.
     *
     * @return JsonResponse
     */
    public function show()
    {
        return new \MotionArray\Http\Resources\UserPrivate(Auth::user());
    }
}

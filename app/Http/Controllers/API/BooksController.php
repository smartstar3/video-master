<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Http\JsonResponse;

class BooksController extends BaseController
{
    public function unlock() {
        session(['books.shared' => true]);

        return new JsonResponse([
            'message' => "Download unlocked",
        ], 200);
    }
}
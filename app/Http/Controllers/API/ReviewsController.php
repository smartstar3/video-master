<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use MotionArray\Repositories\ReviewRepository;

/**
 * Class ReviewsController
 *
 * @package MotionArray\Http\Controllers\API
 */
class ReviewsController extends BaseController
{
    protected $review;

    /**
     * ReviewsController constructor.
     *
     * @param ReviewRepository $review
     */
    public function __construct(ReviewRepository $review)
    {
        $this->review = $review;
    }

    /**
     * Update Review Settings
     *
     * @return mixed
     */
    public function updateSettings()
    {
        if (!Auth::check()) {
            return Reponse::json(['success' => false]);
        }

        $user = Auth::user();

        $settings = Request::input('settings');

        $review = $this->review->findByUser($user);

        if (!$review) {
            $review = $this->review->createReview($user);
        }

        $review = $this->review->updateSettings($review, $settings);

        return Response::json(['success' => true, 'response' => $review]);
    }
}

<?php namespace MotionArray\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MotionArray\Http\Requests\Seller\MessageSellerRequest;
use MotionArray\Http\Requests\Seller\UpdateSellerReviewRequest;
use MotionArray\Jobs\SendSellerReviewNotificationEmail;
use MotionArray\Mailers\ProducerMailer;
use MotionArray\Models\File;
use MotionArray\Models\SellerReview;
use MotionArray\Models\User;
use MotionArray\Repositories\UserRepository;
use MotionArray\Services\SellerStats\SellerStatsService;
use Symfony\Component\HttpFoundation\Request;

class SellersController extends BaseController
{
    protected $user = null;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function reviewsIndex(Request $request, $id)
    {
        $orderBy = $request->get('order_by', 'stars');
        $orderDirection = $request->get('order_direction', 'desc');
        $sellerReviews = SellerReview::whereSellerId($id)
            ->orderBy($orderBy, $orderDirection)
            ->get();

        $response = [];

        foreach ($sellerReviews as $review) {
            $response[] = $review->present()->review();
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $seller = User::whereId($id)->first();
        $seller->seller_display_name = $request->seller_display_name;
        $seller->profile_info = $request->profile_info;
        $seller->save();

        return $seller->present()->seller();
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateProfileImage(Request $request, $id)
    {
        $seller = User::whereId($id)->first();
        $oldFile = File::whereId($seller->profile_image_id)->first();

        $hash = hash_file('sha256', $request->base64);
        $file = new File();
        $file->base64 = $request->base64;
        $file->filename = "sellers/{$id}/profile-image/$hash.png";
        $file->mime_type = 'image/png';
        $file->saveBase64();

        if (!empty($oldFile)) {
            $seller->profile_image_id = null;
            $seller->save();
            $oldFile->delete();
        }

        $seller->profile_image_id = $file->id;
        $seller->save();

        return $seller->present()->seller();
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateHeaderImage(Request $request, $id)
    {
        $seller = User::whereId($id)->first();
        $oldFile = File::whereId($seller->header_image_id)->first();

        $hash = hash_file('sha256', $request->base64);
        $file = new File();
        $file->base64 = $request->base64;
        $file->filename = "sellers/{$id}/profile-image/$hash.png";
        $file->mime_type = 'image/png';
        $file->saveBase64();

        if (!empty($oldFile)) {
            $seller->header_image_id = null;
            $seller->save();
            $oldFile->delete();
        }

        $seller->header_image_id = $file->id;
        $seller->save();

        return $seller->present()->seller();
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function follow(Request $request, $id)
    {
        $seller = User::whereId($id)->first();
        $seller->follow();

        return $seller->present()->seller();
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function unfollow(Request $request, $id)
    {
        $seller = User::whereId($id)->first();
        $seller->unfollow();

        return $seller->present()->seller();
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function AmIFollowing(Request $request, $id)
    {
        $seller = User::whereId($id)->first();
        $amIFollowing = $seller->amIFollowing();

        return ['amIFollowing' => $amIFollowing];
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function followerCount(Request $request, $id)
    {
        $seller = User::whereId($id)->first();
        $count = $seller->getFollowerCount();

        return ['count' => $count];
    }

    /**
     * Gets the products from this seller that has been downloaded by the logged in user.
     *
     * @param int $id
     * @return mixed
     */
    public function myDownloads($id)
    {
        if (!Auth::check()) {
            return [];
        }

        $downloads = DB::table('downloads')
            ->select('products.id', 'products.name')
            ->join('products', 'products.id', '=', 'downloads.product_id')
            ->where('products.seller_id', '=', $id)
            ->where('downloads.user_id', '=', Auth::user()->id)
            ->get();

        return $downloads;
    }

    /**
     * Gets the paid products from this seller that has been downloaded by the logged in user.
     *
     * @param int $id
     * @return mixed
     */
    public function myPaidDownloads($id)
    {
        if (!Auth::check()) {
            return [];
        }

        $downloads = DB::table('downloads')
            ->select('products.id', 'products.name')
            ->join('products', 'products.id', '=', 'downloads.product_id')
            ->where('products.seller_id', '=', $id)
            ->where('downloads.free', '=', 0)
            ->where('downloads.user_id', '=', Auth::user()->id)
            ->get();

        return $downloads;
    }

    /**
     * Get the review for the seller made by the logged in user.
     *
     * @param int $id
     * @return mixed
     */
    public function showMyReview($id)
    {
        if (!Auth::check()) {
            return new JsonResponse(['message' => 'No user is logged in'], 401);
        }
        $myReview = SellerReview::whereSellerId($id)->whereReviewerId(Auth::user()->id)->first();
        if (empty($myReview)) {
            return new JsonResponse(['message' => 'Review not found'], 404);
        }

        return $myReview->present()->review();
    }

    /**
     * Save the review for the seller made by the logged in user.
     *
     * @param UpdateSellerReviewRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateMyReview(UpdateSellerReviewRequest $request, $id)
    {
        if (!Auth::check()) {
            return new JsonResponse(['message' => 'No user is logged in'], 401);
        }
        $myReview = SellerReview::whereSellerId($id)->whereReviewerId(Auth::user()->id)->first();
        if (empty($myReview)) {
            $myReview = new SellerReview();
        }

        $myReview->seller_id = $id;
        $myReview->reviewer_id = Auth::user()->id;
        $myReview->product_id = $request->product_id;
        $myReview->stars = $request->stars;
        $myReview->review = $request->review;
        if ($myReview->save()) {
            $seller = User::whereId($id)->first();
            dispatch(new SendSellerReviewNotificationEmail($seller));
        }

        return new JsonResponse([
            'message' => 'Review saved',
            'review' => $myReview->present()->review()
        ], 201);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function reviewTotals($id)
    {
        $average = DB::table('seller_reviews')
            ->whereSellerId($id)
            ->avg('stars');

        $count = DB::table('seller_reviews')
            ->whereSellerId($id)
            ->count('id');

        return ['average' => $average, 'count' => $count];
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function reviewsSummary($id)
    {
        $reviewSummary = DB::table('seller_reviews')
            ->select(
                'stars',
                DB::raw("count(id) / (SELECT count(id) FROM seller_reviews WHERE seller_id = $id) * 100 as percentage")
            )
            ->groupBy('stars')
            ->orderBy('stars', 'desc')
            ->whereSellerId($id)
            ->get();

        return $reviewSummary;
    }

    /*
     * Sends contact message from producer page contact form.
     *
     * @param MessageSellerRequest $request
     * @param $id
     */
    public function message(MessageSellerRequest $request, $id)
    {
        $seller = User::whereId($id)->first();

        $form['name'] = $request->name;
        $form['email'] = $request->email;
        $form['subject'] = $request->subject;
        $form['message'] = $request->message;

        $producerMailer = new ProducerMailer();

        $producerMailer->producerContactForm($seller, $form);

        return new JsonResponse(['message' => 'Message sent'], 200);
    }

    public function stats($sellerId = null, Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response('Unauthorized.', 401);
        }

        if ($sellerId && $user->isAdmin()) {
            $targetUser = $this->user->findById($sellerId);
        } else {
            $targetUser = $user;
        }

        $month = $request->get("month");

        $year = $request->get("year");

        $dateStart = Carbon::create($year, $month, 1, 0, 0, 0)->startOfMonth();

        $dateEnd = $dateStart->copy()->endOfMonth();

        /** @var SellerStatsService $statsService */
        $statsService = app(SellerStatsService::class);

        $response = $statsService->sellerStats($targetUser, $dateStart, $dateEnd, -1);

        return new JsonResponse($response, 200);
    }
}

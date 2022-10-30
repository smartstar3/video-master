<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use MotionArray\Models\SellerFollower;
use MotionArray\Repositories\PlanRepository;
use Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use MotionArray\Helpers\Helpers;
use MotionArray\Mailers\UserMailer;
use MotionArray\Models\Plan;
use MotionArray\Repositories\UserRepository;
use MotionArray\Repositories\UserUploadRepository;
use Cookie;
class UsersController extends BaseController
{
    protected $user;
    protected $planRepo;

    public function __construct(UserRepository $user, PlanRepository $planRepository)
    {
        $this->user = $user;
        $this->planRepo = $planRepository;
    }

    public function show()
    {
        if (Auth::check()) {
            $user = Auth::user();

            $user->getAttribute('upvotes');

            $user->isSeller = $user->isSeller();

            $user->isAdmin = $user->isAdmin();

            $user->canUpload = $user->canUpload();

            //Available Space
            $diskUsage = Helpers::bytesToKb($this->user->getDiskUsage($user));//This returns bytes so convert to Kb first

            $planSpace = $user->plan->diskSpaceInKb();//This returns Kb

            if ($user->plan->isFree() && $user->isAdmin()) {
                $user->availableSpace = -1;
            } else {
                $user->availableSpace = $planSpace - $diskUsage;
            }

            $user->diskUsage = $diskUsage;

            $response = Response::json($user);
        } else {
            $response = Response::json(['error' => 'No user logged']);
        }

        if (Request::has('callback')) {
            $response = $response->setCallback(Request::get('callback'));
        }

        return $response;
    }

    /**
     * Updates uploading files temp registry
     */
    public function uploading(UserUploadRepository $userUpload)
    {
        if (!Auth::check()) {
            Response::json("Unauthorised user", 401);
        }

        $user = Auth::user();

        $data = Request::all();

        $userUpload->addUploadingRecord($user, $data);

        return Response::json(['success' => true]);
    }

    public function renewalDate()
    {
        if (Auth::check() && (Auth::user()->subscribed() || Auth::user()->isFreeloader())) {
            $renewal_date = Auth::user()->getPeriodRenewalDate()->format('F d, Y');

            return Response::json([
                "renewal_date" => $renewal_date
            ]);
        }

        return Response::json("Unauthorised user", 401);
    }

    public function status()
    {
        if (Auth::check()) {
            return Response::json(["logged_in" => true,
                "is_seller" => Auth::user()->isSeller()]);
        }

        return Response::json(["logged_in" => false]);
    }

    public function isSeller()
    {
        if (Auth::check()) {
            return Response::json(["is_seller" => Auth::user()->isSeller()]);
        }

        return Response::json(["is_seller" => false]);
    }

    public function sendConfirmationEmail(UserMailer $userMailer)
    {
        $user = Auth::user();

        if (!$user) {
            return Response::json(['success' => false]);
        }

        $response = [
            'success' => true,
            'confirmed' => $user->confirmed
        ];

        $userMailer->confirmation($user);

        return Response::json($response);
    }


    public function validatePlanChange()
    {
        $billing_id = Request::get("billing_id");
        $new_plan = Plan::where("billing_id", "=", $billing_id)->first();
        $user = Auth::user();

        if ($this->planRepo->isDowngradingPlans($user->plan, $new_plan)) {

            $diskUsage = Helpers::bytesToKb($this->user->getDiskUsage($user));

            $planSpace = $new_plan->diskSpaceInKb();//This returns Kb

            $overusage = round(Helpers::kbToGb($diskUsage - $planSpace), 2);

            if ($overusage > 0) {
                $message = "To downgrade to the " . $new_plan->display_name . " plan you must delete <strong>" . $overusage . "</strong> GB of files";

                return Response::json(['success' => false, 'message' => $message]);
            }

            return Response::json(['success' => true, 'message' => '']);
        }

        return Response::json(['success' => true, 'message' => '']);
    }

    public function sellersIFollow()
    {
        if (!Auth::check()) {
            return new JsonResponse(['error' => 'No user logged'], 401);
        }

        $sellerFollowers = SellerFollower::whereFollowerId(Auth::user()->id)->get();
        $sellersIFollow = [];
        foreach ($sellerFollowers as $sellerFollower) {
            $sellersIFollow[] = $sellerFollower->seller->present()->seller();
        }

        return $sellersIFollow;
    }

    public function acceptTos()
    {
        if (auth()->check()) {
            $user = auth()->user();

            $this->user->acceptTos($user);
        } else {
            Cookie::queue(Cookie::make('tos', true, /* forever */ 2628000,
                /* path */ null, /* domain */ null, /* secure */ false, /* httponly */ false));
        }

        return ['message' => 'User updated'];
    }
}

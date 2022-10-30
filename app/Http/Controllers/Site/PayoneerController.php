<?php namespace MotionArray\Http\Controllers\Site;

use MotionArray\Repositories\UserRepository;
use MotionArray\Billing\Payoneer;
use Redirect;
use Response;
use App;
use Auth;
use URL;
use Request;

class PayoneerController extends BaseController
{

    /**
     * User Repo
     */
    private $userRepo;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepo = $userRepository;
    }


    /**
     * Generate the Payoneer sign up URL and forward the user to Payoneer
     * to create/connect their account.
     *
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function create()
    {
        // Update user record, setting payoneer_id and payout_method
        $payoneer_id = 'MA' . Auth::user()->id;

        $data = [
            'payout_method' => 'payoneer',
            'payoneer_id' => $payoneer_id
        ];

        if ($user = $this->userRepo->update(Auth::user()->id, $data)) {
            $payoneer = new Payoneer();

            // Generate Payoneer Signup URL.
            $mname = "GetToken"; // sets the mname to GetToken, do not edit

            $p4 = "&p4=" . $payoneer_id; // partner payee ID
            $p6 = "&p6=" . URL::to('/account/seller-details'); // partner payee ID
            $parameters = $p4 . $p6; // sets the p4 parameter to the variable parameters to later be passes through a function

            if (Request::get('p11')) {
                $parameters .= "&p11=" . Request::get('p11');
            }

            $signup_link = $payoneer->openurl($mname, $parameters); // sets the variable signup_link to the function openurl while passing through mname and parameters

            return Redirect::to($signup_link);
        }

        return App::abort('500');
    }


    /**
     * Remove the user's association with their Payoneer account.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        // Reset payoneer_id and payout_method on the user.
        $data = [
            'payoneer_id' => null,
            'payout_method' => null,
            'payoneer_confirmed' => false
        ];

        if ($user = $this->userRepo->update(Auth::user()->id, $data)) {
            return Response::json('Your Payoneer account has been disconnected.', 200);
        }

        return Response::json('There was a problem disconnecting your Payoneer account.', 500);
    }
}

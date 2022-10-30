<?php namespace MotionArray\Http\Controllers\Shared;

use MotionArray\Models\User;
use Auth;
use View;
use Request;
use Redirect;
use Response;
use Flash;
use Session;

class SessionsController extends BaseController
{
    public $createView;
    public $intendedDefault;
    public $destroyRedirect;
    public $accessLevel = [];
    public $forceRememberMe = false;

    public function login()
    {
        $input = Request::all();

        // Add parameter when the user is forced to logout.
        $forceLogout = 0;
        if (isset($input["ref"]) && $input["ref"] == 1) {
            $forceLogout = 1;
        }

        return View::make($this->createView)->with('force_logout', $forceLogout);
    }

    public function storeSession()
    {
        $input = Request::all();

        $errorMessage = "Invalid email address/password";

        if ($this->checkRole($input["email"])) {
            $rememberMe = true;

            if (!$this->forceRememberMe) {
                $rememberMe = $input["remember_me"] === "true" ? true : false;
            }

            $attempt = Auth::attempt([
                "email" => $input["email"],
                "password" => $input["password"]
            ], $rememberMe);

            if ($attempt) {
                Auth::logoutOtherDevices($input["password"]);

                $user = Auth::user();

                if($user->disabled == 1) {
                    Auth::logout();

                    $errorMessage = "Your account has been put on hold. Please contact us.";

                } else {
                    $session_id = Session::getId();

                    $user->session_id = $session_id;

                    $user->save();

                    if (request()->ajax() || request()->wantsJson()) {
                        return response()->json([
                            'status' => 'success',
                            'token' => csrf_token(),
                            'user_id' => Auth::user()->id
                        ]);
                    }

                    if ($this->intendedDefault) {
                        return Redirect::intended($this->intendedDefault);
                    } else {
                        if ($user->plan->isFree()) {
                            return Redirect::to('/account/upgrade');
                        } else {
                            return Redirect::to('/browse');
                        }
                    }
                }
            }
        }


        // @TODO we need to return 401
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $errorMessage
            ], 400);
        }

        Flash::danger($errorMessage);

        return Redirect::back();
    }

    public function checkSession()
    {
        return response()->json([
            'is_logged_in' => Auth::check()
        ]);
    }

    public function checkRole($email)
    {
        $user = User::where("email", "=", $email)->first();

        if ($user) {
            $roles = $user->roles()->get();

            foreach ($roles as $role) {
                if (in_array($role->id, $this->accessLevel)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function logout()
    {
        Auth::logout();

        return Redirect::to($this->destroyRedirect);
    }
}

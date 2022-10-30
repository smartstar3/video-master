<?php namespace MotionArray\Http\Controllers\Shared;

use App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use MotionArray\Mailers\UserMailer;
use View;
use Redirect;
use Request;
use Lang;
use Hash;

class RemindersController extends BaseController
{
    public $getRemindView;
    public $getResetView;
    public $resetRedirect;

    /**
     * Display the password reminder view.
     *
     * @return Response
     */
    public function getRemind()
    {
        return View::make($this->getRemindView);
    }

    /**
     * Handle a POST request to remind a user of their password.
     *
     * @return Response
     */
    public function postRemind()
    {
        $response = Password::sendResetLink(Request::only('email'), function ($message) {
            $message->subject("Forgotten password reminder");
        });

        switch ($response) {
            case Password::INVALID_USER:
                return Redirect::back()->with('error', Lang::get($response));

            case Password::RESET_LINK_SENT:
                return Redirect::back()->with('status', Lang::get($response));
        }
    }

    /**
     * Display the password reset view for the given token.
     *
     * @param  string $token
     *
     * @return Response
     */
    public function getReset($token = null)
    {
        if (is_null($token)) App::abort(404);

        return View::make($this->getResetView)->with('token', $token);
    }

    /**
     * Handle a POST request to reset a user's password.
     *
     * @return Response
     */
    public function postReset()
    {
        $credentials = Request::only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = Password::reset($credentials, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
            Auth::login($user);
            $mailer = new UserMailer();
            $mailer->importantChange($user, 'password');
        });

        switch ($response) {
            case Password::INVALID_PASSWORD:
            case Password::INVALID_TOKEN:
            case Password::INVALID_USER:
                return Redirect::back()->with('error', Lang::get($response));

            case Password::PASSWORD_RESET:
                return Redirect::to($this->resetRedirect);
        }
    }
}

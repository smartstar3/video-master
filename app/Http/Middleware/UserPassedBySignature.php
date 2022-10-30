<?php

namespace MotionArray\Http\Middleware;

use Closure;
use Session;
use MotionArray\Models\User;

class UserPassedBySignature
{
    /**
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->hasValidSignature()) {
            if(auth()->user()) {
                return $next($request);
            }

            $userId = $request->route('user');
            $user = User::find($userId);

            if($user) {
                auth()->login($user);

                // There is one field that is used for force logout. We should use the field for adobe panel too,
                // so that multiple users cannot use same credential.
                $user->session_id = Session::getId();
                $user->save();

                return $next($request);
            }
        }

        return redirect('/account/login');
    }
}

<?php

namespace MotionArray\Http\Middleware;

use App;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class UpdateIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \session()->forget('user_ips');

        /*
        $ip = $request->ip();

        $removeIp = '186.77.199.225';

        $userIps = \session()->get('user_ips', []);

        if (!in_array($ip, $userIps)) {
            if ($ip != $removeIp) {
                $userIps[] = $ip;

                \session()->put('user_ips', $userIps);
            }
        }

        if (in_array($removeIp, $userIps)) {
            $userIps = array_filter($userIps, function ($ip) use ($removeIp) {
                return $ip != $removeIp;
            });

            \session()->put('user_ips', $userIps);
        }

        if (Auth::check()) {
            $user = Auth::user();

            foreach ($userIps as $userIp) {
                $now = Carbon::now();

                $ip = $user->userIps()->firstOrCreate(['ip' => $userIp]);

                // If it existed, update "updated_at" date
                if ($ip && $ip->updated_at < $now) {
                    $ip->updated_at = $now;
                    $ip->save();
                }
            }
        }*/

        return $next($request);
    }
}

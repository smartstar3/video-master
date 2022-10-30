<?php

namespace MotionArray\Http\Middleware;

use Closure;

class Terminable
{
    var $blacklist = [
        '_debugbar/open'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        //To use this, add APP_INPUT_LOG=true to your .env
        if (\Config::get('app.input-log') && !in_array($request->path(), $this->blacklist)) {
            \Illuminate\Support\Facades\Log::info($request->method() . " " . $request->path() . " params:" . json_encode($request->all()));
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {
        //To use this, add APP_OUTPUT_LOG=true to your .env
        if (\Config::get('app.output-log')) {
            $requestTypeRaw = explode(';', $request->getContentType());

            $requestType = "";
            if (is_array($requestTypeRaw) && isset($requestTypeRaw[0])) {
                $requestType = $requestTypeRaw[0];
            }

            if (!in_array($request->path(), $this->blacklist) && $requestType == "json") {
                \Illuminate\Support\Facades\Log::info("RESPONSE" . " " . $request->path() . " data:" . $response->content());
            }
        }
    }
}

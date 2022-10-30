<?php

namespace MotionArray\Http\Controllers\Site;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class RedirectController extends BaseController
{
    /**
     * Redirects SOMETHING/X/Y/Z to SOMETHING_ELSE/X/Y/Z
     *
     * @return RedirectResponse
     */
    public function browse(...$args)
    {
        $url = array_pop($args); // The prefix will be sent last to the function
        if (count($args)) {
            $url .= "/" . implode('/', $args);
        }
        $query = Request::query();
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        return Redirect::to($url, 301);
    }

    public function product($to)
    {
        $url = $to;
        $query = Request::query();
        if ($query) {
            $url .= '?' . http_build_query($query);
        }
        return Redirect::to($url, 301);
    }
}

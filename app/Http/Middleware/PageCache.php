<?php

namespace MotionArray\Http\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Closure;
use Cache;
use Auth;
class PageCache
{
    public function handle(Request $request, Closure $next)
    {
        $uri = $request->getRequestUri();
        $full_url = $request->fullUrl();

        // Only cache the page if full-page cache enabled, GET request,
        // not logged in and matches full page cache URI
        $use_cache = (config('cache.page') and $request->isMethod('GET')
            and Auth::guest() and $this->uri_match($uri));

        // Cache on the full URL as multiple hostnames may be used in tandem
        $cache_key = "Page-{$full_url}";
        if ($use_cache and Cache::has($cache_key))
        {
            $response = $this->unpack_response(Cache::get($cache_key));
            $response = $this->replace_csrf_token($response);
            $response->headers->set('X-Page-Cached', true);
        } else {
            $response = $next($request);
            if ($use_cache and $response->getStatusCode() == 200)
               Cache::put($cache_key, $this->pack_response($response), 10);
        }
        return $response;
    }
    /**
     * Adds CSRF token dynamically to the cached response
     *
     * @param Response $response
     * @return Response
     */
    protected function replace_csrf_token($response)
    {
        // @FIXME(abiusx): use cookie based CSRF tokens
        if ($response instanceof BinaryFileResponse) return $response; // No change
        $csrf_token = csrf_token();

        $html = $response->getContent();
        $html_head = substr($html, 0, 2048); // the meta tag should be in the first 2KB, and this makes the processing faster
        $html_tail = substr($html, 2048);

        // NOTE: the replace string should use $1 normally, but ${1} when 1 is followed by  a number.
        // since we don't know if first char of token is a number or not, I separate the double quotes and use it
        // manually in the replace string.
        $html_head = preg_replace('/(<meta name="_token" content=)"(.*?)("\s*\/>)/',"$1\"{$csrf_token}$3",$html_head);

        $html = $html_head . $html_tail;

        $new_response = new Response($html, $response->getStatusCode());
        $new_response->headers = $response->headers;
        return $new_response;
    }
   /**
     * Check if URI matches cached pages configuration list
     * e.g. /,/browse.*
     * @param string $uri
     * @return bool
     */
    protected function uri_match(string $uri)
    {
        // Making sure the URI matches cached pages
        foreach (config('cache.pages') as $page_pattern) {
            $pattern = '/^' . str_replace('/', '\/', trim($page_pattern)) . '$/';
            if (preg_match($pattern, $uri))
                return true;
        }
        return false;
    }
    /**
     * Packs a response object into necessary ingredients
     * A normal response object has references to the container
     * and thus cannot be properly serialized (closure error).
     *
     * @param Response $response
     * @return array
     */
    protected function pack_response(Response $response): array
    {
        $statusCode = $response->getStatusCode();
        $headers = $response->headers;
        if ($response instanceof BinaryFileResponse) {
            $content = $response->getFile()->getPathname();
            $type = 'file';
            $obj = compact('statusCode', 'headers', 'content', 'type');
        } else {
            $content = $response->getContent();
            $type = 'normal';
            $obj = compact('statusCode', 'headers', 'content', 'type');
        }
        return $obj;
    }
    /**
     * Unpacks a packed response object
     *
     * @param array $responeObj
     * @return Response
     */
    protected function unpack_response(array $responeObj): Response
    {
        $type = $responeObj['type'] ?? 'normal';
        if ($type === 'file') {
            $response = new BinaryFileResponse(
                $responeObj['content'],
                $responeObj['statusCode']
            );
        } else {
            $response = new Response($responeObj['content'], $responeObj['statusCode']);
        }
        $response->headers = $responeObj['headers'];
        return $response;
    }
}
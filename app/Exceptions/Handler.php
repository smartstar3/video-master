<?php

namespace MotionArray\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        TokenMismatchException::class,
        OAuthServerException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     *
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->isJson()) {
            return $this->renderJsonException($request, $exception);
        } else {
            if ($exception instanceof TokenMismatchException) {
                session()->flash('flash_notification.message', 'Validation Token was expired. Please try again');
                session()->flash('flash_notification.level', 'danger');

                return redirect()
                    ->to($request->url())
                    ->withInput($request->except('password'));
            }

            return parent::render($request, $exception);
        }
    }

    protected function decorate($content, $css)
    {
        if (config('app.debug')) {
            return parent::decorate($content, $css);
        } else {

            return require base_path() . '/resources/views/site/errors/error.blade.php';
        }
    }

    /**
     * Render the given HttpException.
     *
     * @param  \Symfony\Component\HttpKernel\Exception\HttpException $exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(HttpException $exception)
    {
        $status = $exception->getStatusCode();

        if (!isMotionArrayDomain()) {
            if (view()->exists("site.portfolio.errors.{$status}"))
                return response()->view("site.portfolio.errors.{$status}");
            return redirect()->to(config('app.url'));
        }

        if (view()->exists("site.errors.{$status}")) {
            return response()->view("site.errors.{$status}", ['exception' => $exception], $status, $exception->getHeaders());
        } else {
            return $this->convertExceptionToResponse($exception);
        }
    }

    /**
     * Render JSON exceptions for API calls.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function renderJsonException($request, Exception $exception)
    {
        $response['message'] = $exception->getMessage();
        if (is_object($response['message'])) {
            $response['message'] = $response['message']->toArray();
        }
        switch (true) {
            case $exception instanceof TokenMismatchException:
                $response['message'] = "Token does not match";
                $statusCode = 403;
                break;
            case $exception instanceof HttpException:
                $statusCode = $exception->getStatusCode();
                break;
            case $exception instanceof \Illuminate\Auth\AuthenticationException:
                $response['message'] = 'You are logged out because your session timed out or you logged in from another device';
                $response['error'] = [
                  'code' => 1,
                  'type' => 'SESSION_EXPIRED, MULTIPLE_LOGIN'
                ];
                $statusCode = 401;
                break;
            case $exception instanceof ModelNotFoundException:
                $response['message'] = 'Resource not found';
                $statusCode = 404;
                break;
            case $exception instanceof ValidationException:
                $response['errors'] = $exception->validator->getMessageBag()->getMessages();
                $statusCode = 422;
                break;
            default:
                $statusCode = 500;
                break;
        }

        if (config('app.debug')) {
            $response['exception'] = get_class($exception);
            $response['trace'] = $exception->getTrace();
        }

        return new JsonResponse($response, $statusCode);
    }
}

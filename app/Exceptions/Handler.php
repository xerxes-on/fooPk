<?php

namespace App\Exceptions;

use Accentinteractive\LaravelBlocker\Exceptions as Blocker;
use Accentinteractive\LaravelBlocker\Facades\BlockedIpStore;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Spatie\LaravelIgnition\Exceptions\ViewException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        Blocker\BlockedUserException::class,
        Blocker\MaliciousUrlException::class,
        Blocker\MaliciousUserAgentException::class
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // $this->reportable(
        //     function (Throwable $e) {
        //         //
        //     }
        // );
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Exit immediately if the exception is from malicious user or URL
        if (in_array(get_class($e), [Blocker\BlockedUserException::class, Blocker\MaliciousUrlException::class, Blocker\MaliciousUserAgentException::class])) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }

        // Block the IP if the request is suspicious
        if ($e instanceof SuspiciousOperationException) {
            BlockedIpStore::create($request->ip());
            return response()->json(['message' => 'Not accepted'], ResponseAlias::HTTP_NOT_ACCEPTABLE);
        }

        // additional error log data
        if (config('app.debug')) {
            $additionalExceptionData = [
                'url'      => url()->full(),
                '$_SERVER' => $_SERVER,
            ];

            if (!empty($_REQUEST)) {
                if (strlen(json_encode($_REQUEST)) < 50000) {
                    $additionalExceptionData['$_REQUEST'] = $_REQUEST;
                } else {
                    $additionalExceptionData['$_REQUEST'] = 'too_big_size:' . strlen(json_encode($_REQUEST));
                }
            }

            if (!empty($_POST)) {
                if (strlen(json_encode($_POST)) < 50000) {
                    $additionalExceptionData['$_POST'] = $_POST;
                } else {
                    $additionalExceptionData['$_POST'] = 'too_big_size:' . strlen(json_encode($_POST));
                }
            }

            $http_get_request_body = @file_get_contents('php://input');
            if (!empty($http_get_request_body)) {
                if (strlen(json_encode($http_get_request_body)) < 50000) {
                    $additionalExceptionData['php_input'] = $http_get_request_body;
                } else {
                    $additionalExceptionData['php_input'] = 'too_big_size:' . strlen(json_encode($http_get_request_body));
                }
            }

            \Log::error('ERROR:' . $e . var_export($additionalExceptionData, true));
        }

        // Need to gather more data for ViewException
        if ($e instanceof ViewException) {
            logError($e->getMessage(), [
                'route'   => $request->route()->getName(),
                'url'     => $request->fullUrl(),
                'agent'   => $request->userAgent(),
                'request' => $request->all()
            ]);
        }

        // redirect back to login page when 419 error happens
        if ($e instanceof TokenMismatchException) {
            return redirect()->route('login')->with('message', trans('common.token_mismatch_error'));
        }
        return parent::render($request, $e);
    }
}

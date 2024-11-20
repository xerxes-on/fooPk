<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to check if the request is coming from the designated admin server.
 */
final class CheckDesignatedAdminServerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!app()->environment('production')) {
            return $next($request);
        }
        $adminHost     = config('app.url_static');
        $requestedHost = $request->schemeAndHttpHost();
        if ($requestedHost === $adminHost) {
            return $next($request);
        }

        if ($requestedHost === config('app.url_meinplan')) {
            return redirect()->secure($adminHost . route('login.admin', absolute: false));
        }
        return $next($request);
    }
}

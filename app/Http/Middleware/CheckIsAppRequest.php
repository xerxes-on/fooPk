<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIsAppRequest
{
    /**
     * Handle an incoming request.
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @deprecated
     */
    public function handle(Request $request, Closure $next)
    {
        // TODO:: is it active still??? review all "is_app" checking @NickMost
        if ($request->has('isApp') || $request->has('is_app')) {
            \Cookie::queue(cookie()->forever('is_app', (string)time()));
        }
        return $next($request);
    }
}

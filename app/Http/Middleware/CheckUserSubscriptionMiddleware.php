<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Check user challenge middleware
 * TODO: maybe rename it to UserSubscriptionMiddleware
 * @package App\Http\Middleware
 */
final class CheckUserSubscriptionMiddleware
{
    /**
     * Handle absence of a challenge.
     * TODO:: refactor this into active-subscription
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return is_null(\Auth::user()->subscription) ?
            (
                $request->acceptsJson() ?
                response()->json(['success' => false, 'message' => trans('common.no_available_course')]) :
                redirect()->back()->with('error', trans('common.no_available_course'))
            ) :
            $next($request);
    }
}

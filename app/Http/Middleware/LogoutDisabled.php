<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Logout disabled users.
 *
 * @package App\Http\Middleware
 */
final class LogoutDisabled
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!is_null($user) && false === $user->status) {
            $this->performLogout($request);

            return $request->expectsJson() ?
                response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => trans('auth.user_suspended'),
                    'errors'  => 'Unauthorized',
                ], ResponseAlias::HTTP_FORBIDDEN) :
                redirect('/')->with('warning', trans('auth.user_suspended'));
        }

        return $next($request);
    }

    private function performLogout(Request $request): void
    {
        // For web guard
        if (method_exists(Auth::guard(), 'logout')) {
            Auth::logout();
            return;
        }
        // For api guard
        $request->user()->tokens()->delete();
    }
}

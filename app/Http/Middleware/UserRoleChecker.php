<?php

namespace App\Http\Middleware;

use App\Enums\Admin\Permission\PermissionEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * checkRole.user middleware.
 *
 * @package App\Http\Middleware
 */
final class UserRoleChecker
{
    /**
     * Handle user without "user" role.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        if (is_null($user)) {
            return $next($request);
        }

        if ($user->hasRole('admin')) {
            return $user->hasPermissionTo(PermissionEnum::SEE_ALL_CLIENTS->value) ?
                redirect()->route('admin.model', ['adminModel' => 'users']) :
                redirect()->route('admin.dashboard');
        }

        if (config('app.env') !== 'production') {
            return $next($request);
        }

        // Prevent user to go to specific production server
        $currentUrlRequested = parse_url($request->url());
        $currentUrlAllowed   = parse_url(config('app.url_meinplan'));
        if (
            isset($currentUrlRequested['host']) &&
            isset($currentUrlAllowed['host']) &&
            $currentUrlRequested['host'] !== $currentUrlAllowed['host']
        ) {
            return redirect()->to(config('app.url_meinplan'), ResponseAlias::HTTP_SEE_OTHER);
        }
        return $next($request);
    }
}

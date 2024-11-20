<?php

namespace App\Http\Middleware;

use App\Enums\Admin\Permission\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class AdminRoleChecker
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()?->hasAnyRole(RoleEnum::getAdminRoles())) {
            return (new LangManager())->handle($request, $next);
        }

        return redirect()->route('recipes.list');
    }
}

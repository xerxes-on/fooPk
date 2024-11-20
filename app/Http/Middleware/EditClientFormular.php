<?php

namespace App\Http\Middleware;

use App\Enums\Admin\Permission\PermissionEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EditClientFormular
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->hasPermissionTo(PermissionEnum::MANAGE_CLIENT_FORMULAR->value)) {
            return redirect()->back()->with('error_message', 'Action forbidden');
        }

        return $next($request);
    }
}

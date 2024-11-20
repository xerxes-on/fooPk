<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * check.formular middleware.
 *
 * @package App\Http\Middleware
 */
final class CheckUserFormularExistence
{
    /**
     * Handle case when a user lacks formular.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        return \Auth::user()->isQuestionnaireExist() ? $next($request) : redirect()->route('questionnaire.create');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Admin\Permission\RoleEnum;
use Auth;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Jenssegers\Date\Date;
use Symfony\Component\HttpFoundation\Response;

/**
 * Language middleware
 *
 * @package App\Http\Middleware
 */
final class LangManager
{
    private bool $isAdmin = false;

    /**
     * Set current user language.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $this->getLocale($request);

        app()->setLocale($lang);

        try {
            app()->make('config')->set('translatable.locale', $lang);
        } catch (BindingResolutionException $e) {
            logError($e);
        }

        if (session()->missing('translatable_lang')) {
            session()->put('translatable_lang', $lang);
        }

        Date::setLocale($lang);

        if ($this->isAdmin && !\Cookie::has('translatable_lang')) {
            \Cookie::queue('translatable_lang', $lang, config('session.lifetime'));
        }

        return $next($request);
    }

    private function getLocale(Request $request): string
    {
        // check for Admins
        if (Auth::guard(RoleEnum::ADMIN_GUARD)->check()) {
            $this->isAdmin = true;
            return Auth::guard(RoleEnum::ADMIN_GUARD)->user()->lang;
        }

        if (Auth::check()) {
            return Auth::user()->lang;
        }
        $possibleAppLang = $request->input('lang', config('app.locale'));
        return in_array($possibleAppLang, config('app.locales')) ? $possibleAppLang : config('app.locale');
    }
}

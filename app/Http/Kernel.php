<?php

declare(strict_types=1);

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * HTTP Kernel
 *
 * @package App\Http
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        \App\Http\Middleware\TrustHosts::class,
        \Accentinteractive\LaravelBlocker\Http\Middleware\BlockMaliciousUsers::class,
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Spatie\CookieConsent\CookieConsentMiddleware::class,
        \App\Http\Middleware\CheckIsAppRequest::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            'encrypt.cookies',
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            # force to https
            \App\Http\Middleware\HttpsProtocol::class,
            # user auto logout
            \App\Http\Middleware\LogoutDisabled::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            'bindings',
            'encrypt.cookies',
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            'lang.manager',
            # user auto logout
            \App\Http\Middleware\LogoutDisabled::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth'                     => \App\Http\Middleware\Authenticate::class,
        'auth.basic'               => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'                 => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'auth.session'             => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers'            => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'                      => \Illuminate\Auth\Middleware\Authorize::class,
        'checkRole.admin'          => \App\Http\Middleware\AdminRoleChecker::class,
        'checkRole.user'           => \App\Http\Middleware\UserRoleChecker::class,
        'guest'                    => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm'         => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'                   => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'                 => \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        'verified'                 => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'check.questionnaire'      => \App\Http\Middleware\CheckUserFormularExistence::class,
        'check.challenge'          => \App\Http\Middleware\CheckUserSubscriptionMiddleware::class,
        'check.recipeAccess'       => \App\Http\Middleware\CheckUserAccessToRecipe::class,
        'check.customRecipeAccess' => \App\Http\Middleware\CheckUserAccessToCustomRecipe::class,
        'lang.manager'             => \App\Http\Middleware\LangManager::class,
        'encrypt.cookies'          => \App\Http\Middleware\EncryptCookies::class,
        'abilities'                => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        'canEditClientFormular'    => \App\Http\Middleware\EditClientFormular::class,
        'cache.web.policy.noCache' => \App\Http\Middleware\CachePolicyNoCache::class,
        'check.adminServer'        => \App\Http\Middleware\CheckDesignatedAdminServerMiddleware::class,
    ];
}

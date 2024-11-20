<?php

declare(strict_types=1);

namespace App\View;

use App\Enums\Admin\Permission\RoleEnum;
use Cache;
use Illuminate\View\View;

/**
 * Share global data with designated views.
 *
 * @package App\View
 */
final class GlobalVariableComposer
{
    // TODO: Probably should be redesigned to use for public and admin sides
    public const PUBLIC_VIEWS = [
        'layouts.app',
        'layouts.flash-messages',
        'layouts.inc.site-navigation',
        'dashboard.dashboard',
        'user.settings.components.accountSettingsTab'
    ];

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user             = auth()->user();
        $aboChallengeData = null;
        if (!is_null($user) && $user->hasRole(RoleEnum::USER->value)) {
            $aboChallengeData = Cache::get('user-' . $user->id . '-aboChallengeData');
            if (is_null($aboChallengeData)) {
                $aboChallengeData = $user?->getCourseData();
                Cache::put('user-' . $user->id . '-aboChallengeData', $aboChallengeData ?? '', config('cache.lifetime_short'));
            }
        }
        $aboChallengeIsNotOver = is_array($aboChallengeData) && ($aboChallengeData['curDay'] <= $aboChallengeData['duration']);
        $isApp                 = request()->has('is_app');
        $hasAppCookie          = \Cookie::get('is_app');
        $currentRoute          = \Route::current();

        $view->with('aboChallengeData', $aboChallengeData);
        $view->with('aboChallengeIsNotOver', $aboChallengeIsNotOver);
        $view->with('isApp', $isApp);
        $view->with('hasAppCookie', $hasAppCookie);
        $view->with('currentRoute', $currentRoute);
    }
}

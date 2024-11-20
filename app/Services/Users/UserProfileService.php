<?php

namespace App\Services\Users;

use App\Events\UserProfileChanged;
use App\Exceptions\PublicException;
use App\Http\Requests\Profile\UserSettingsFormRequest;

final class UserProfileService
{
    /**
     * Process user sittings update.
     *
     * @throws \App\Exceptions\PublicException
     */
    public function processStore(UserSettingsFormRequest $request): void
    {
        // TODO: modify validate and process date inside form request handler
        $user             = $request->user();
        $user->first_name = $request->first_name;
        $user->last_name  = $request->last_name;
        $user->lang       = $request->lang;

        if (!empty($request->old_password) && !empty($request->new_password)) {
            if (!\Hash::check($request->old_password, $user->password)) {
                throw new PublicException(trans('common.current_password_is_incorrect'));
            }
            $user->password = \Hash::make($request->new_password);
        }

        if (is_string($request->notifications)) {
            $user->push_notifications = $request->notifications;
        }

        $user->save();

        // Need to clear some cache if user changed language
        UserProfileChanged::dispatchif($request->lang);
    }
}

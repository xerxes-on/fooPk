<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

/**
 * Password Reset Controller
 *
 * This controller is responsible for handling password reset emails and
 * includes a trait which assists in sending these notifications from
 * your application to your users. Feel free to explore this trait.
 *
 * @package App\Http\Controllers\Auth
 */
class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user.
     *
     * @param \App\Http\Requests\ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|string
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse|RedirectResponse|string
    {
        /**@var \App\Models\User $user */
        $user = $this->broker()->getUser($request->only('email'));

        if ($user && $user->status === false) {
            $status = 'passwords.disabled';
        } else {
            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $status = $this->broker()->sendResetLink($request->only('email'));
        }
        $locale  = strval($user?->preferredLocale() ?? config('app.locale'));
        $message = strval(trans(key: $status, locale: $locale));

        if ($status === Password::RESET_LINK_SENT) {
            return $this->sendResetLinkResponse($request, $message);
        }

        return $this->sendResetLinkFailedResponse($request, $message);
    }
}

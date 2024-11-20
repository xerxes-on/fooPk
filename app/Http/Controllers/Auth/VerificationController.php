<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Email Verification Controller.
 *
 * This controller is responsible for handling email verification for any
 * user that recently registered with the application. Emails may also
 * be re-sent if the user didn't receive the original email message.
 *
 * @package App\Http\Controllers\Auth
 */
final class VerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request): View
    {
        if (!$request->hasValidSignature()) {
            return view('layouts.choose_device', [
                'language' => 'de',
                'verify'   => true,
                'message'  => trans('auth.email_verification.error')
            ]);
        }

        if (is_null($request->user)) {
            return view('layouts.choose_device', [
                'language' => 'de',
                'verify'   => true,
                'message'  => trans('auth.email_verification.error')
            ]);
        }

        if (!hash_equals((string)$request->user->getKey(), (string)$request->route('id'))) {
            return view('layouts.choose_device', [
                'language' => $request->user->lang,
                'verify'   => true,
                'message'  => trans(
                    'auth.email_verification.error_with_link',
                    ['link' => route('verification.resend', ['id' => $request->user->id])],
                    $request->user->lang
                )
            ]);
        }

        if (!hash_equals(sha1($request->user->getEmailForVerification()), (string)$request->route('hash'))) {
            return view('layouts.choose_device', [
                'language' => $request->user->lang,
                'verify'   => true,
                'message'  => trans(
                    'auth.email_verification.error_with_link',
                    ['link' => route('verification.resend', ['id' => $request->user->id])],
                    $request->user->lang
                )
            ]);
        }

        if (!$request->user->hasVerifiedEmail()) {
            $request->user->markEmailAsVerified();

            event(new Verified($request->user));
            return view(
                'layouts.choose_device',
                [
                    'language' => $request->user->lang,
                    'verify'   => true,
                    'message'  => trans('auth.email_verification.success', locale: $request->user->lang)
                ]
            );
        }

        return view('layouts.choose_device', [
            'language' => $request->user->lang,
            'verify'   => true,
            'message'  => trans('auth.email_verification.already_verified', locale: $request->user->lang)
        ]);
    }

    public function resend(Request $request): View
    {
        $user = User::whereId($request->route('id'))->first();
        if (is_null($user)) {
            return view('layouts.choose_device', [
                'language' => 'de',
                'verify'   => true,
                'message'  => trans('auth.email_verification.error')
            ]);
        }

        $user->sendEmailVerificationNotification();

        return view('layouts.choose_device', [
            'language' => $user->lang,
            'verify'   => true,
            'message'  => trans('auth.email_verification.required', ['email' => $user->email])
        ]);
    }
}

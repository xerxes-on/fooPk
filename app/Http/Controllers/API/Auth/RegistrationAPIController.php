<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Enums\Admin\Permission\RoleEnum;
use App\Exceptions\PublicException;
use App\Http\Controllers\API\APIBase;
use App\Http\Requests\API\Auth\EmailConfirmationRequest;
use App\Http\Requests\API\Auth\EmailVerificationRequest;
use App\Http\Requests\API\Auth\FinishUserRegistrationRequest;
use App\Services\Questionnaire\Converter\Create\QuestionnaireCreateAPIConverterService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\{JsonResponse};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * The controller handles user registration.
 *
 * @note All rotes here are not protected by middleware. So no user exist in request!
 *
 * @package App\Http\Controllers\API
 */
final class RegistrationAPIController extends APIBase
{
    /**
     * Resend email confirmation letter.
     *
     * @route POST /api/v1/registration/send-email-confirmation
     */
    public function resendVerifyEmail(EmailConfirmationRequest $request): JsonResponse
    {
        if ($request->user->hasVerifiedEmail()) {
            $this->sendError(
                'already_verified',
                trans('api.auth.errors.email_already_verified', locale: $request->user->lang),
                ResponseAlias::HTTP_ACCEPTED
            );
        }

        $request->user->sendEmailVerificationNotification();

        return $this->sendResponse(true, trans('api.auth.success.email_verification_sent', locale: $request->user->lang));
    }

    /**
     * Resend email confirmation letter.
     *
     * @route POST /api/v1/registration/verify-email
     */
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        if (!$request->user->hasVerifiedEmail()) {
            $request->user->markEmailAsVerified();

            event(new Verified($request->user));
            return $this->sendResponse(null, trans('auth.email_verification.success_api', locale: $request->user->lang));
        }

        return $this->sendError(null, trans('auth.email_verification.already_verified', locale: $request->user->lang));
    }

    /**
     * Check whether user confirmed email.
     *
     * @route POST /api/v1/registration/check-email-verification
     */
    public function checkConfirmation(EmailConfirmationRequest $request): JsonResponse
    {
        $hasVerified = $request->user->hasVerifiedEmail();
        return $this->sendResponse(
            $hasVerified,
            $hasVerified ?
                trans('api.auth.errors.email_already_verified', locale: $request->user->lang) :
                trans('api.auth.errors.email_not_verified', locale: $request->user->lang)
        );
    }

    /**
     * Check whether user confirmed email.
     * @note Due to mobile implementation specifics, validation is performed here intentionally.
     * Please keep it here in order to show expected data structure for mobile developers.
     * @route POST /api/v1/registration/finish-registration
     */
    public function finalizeRegistration(
        FinishUserRegistrationRequest          $request,
        QuestionnaireCreateAPIConverterService $service
    ): JsonResponse {
        // Set locale for validation messages as user is not authenticated and improper message can be served.
        app()->setLocale($request->user->lang);
        try {
            Validator::make(
                [
                    'email'                 => $request->email,
                    'password'              => $request->password,
                    'password_confirmation' => $request->password_confirmation,
                    'fingerprint'           => $request->fingerprint
                ],
                [
                    'email'       => ['required', 'string', 'email'],
                    'password'    => ['required', 'string', 'confirmed', Password::min(8)->uncompromised()],
                    'fingerprint' => ['required', 'string']
                ]
            )->validate();
        } catch (ValidationException $e) {
            return $this->sendError(
                'validation_error',
                $e->getMessage(),
                ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $request->user->password = Hash::make($request->password);
        $request->user->save();
        if (!$request->user->hasRole(RoleEnum::USER->value)) {
            $request->user->assignRole(RoleEnum::USER->value);
        }

        try {
            $service->convertFromTemporary($request->user, $request->fingerprint);
        } catch (PublicException $e) {
            return $this->sendError(
                'missing_answers_data',
                $e->getMessage(),
                ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return $this->sendResponse(
            [
                'token' => $request->user->createToken(
                    Str::limit($request->userAgent() ?? 'unknown device', 120)
                )->plainTextToken,
                'user' => [
                    'id'         => $request->user->id,
                    'first_name' => $request->user->first_name,
                    'email'      => $request->user->email,
                    'lang'       => $request->user->lang,
                ],
            ],
            trans('api.auth.success.login_success', locale: $request->user->lang)
        );
    }
}

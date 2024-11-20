<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\APIBase;
use App\Http\Requests\API\Auth\{ChangePasswordRequest, LoginRequest};
use App\Models\User;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Hash;
use Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * The controller handles authentication.
 * TODO: Refactor controller to ensure correct throttling.
 * @package App\Http\Controllers\API\Auth
 */
final class AuthApiController extends APIBase
{
    /**
     * Provide access token.
     *
     * @route POST /api/v1/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::ofEmail($request->email)->first();

        if (is_null($user) || !Hash::check($request->password, $user->password)) {
            return $this->sendError(
                message: trans('api.incorrect_creds'),
                status: ResponseAlias::HTTP_UNAUTHORIZED
            );
        }

        if (false === $user->status) {
            return $this->sendError(
                message: trans('auth.user_suspended'),
                status: ResponseAlias::HTTP_FORBIDDEN
            );
        }

        return $this->sendResponse(
            [
                'token' => $user->createToken(Str::limit($request->userAgent() ?? 'unknown device', 120))->plainTextToken,
                'user'  => [
                    'id'         => $user->id,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->email,
                    'lang'       => $user->lang,
                    'status'     => $user->status,
                    'notes'      => $user->notes,
                ],
            ],
            trans('api.login_success')
        );
    }

    /**
     * Remove access token & logout.
     *
     * @route POST /api/v1/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return $this->sendResponse(null, trans('api.logout_success'));
    }

    /**
     * Change password.
     *
     * @route POST /api/v1/change-password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user           = $request->user();
        $user->password = Hash::make($request->new_password);
        $user->save();
        return $this->sendResponse([], trans('common.success'));
    }
}

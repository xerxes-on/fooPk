<?php

namespace App\Http\Traits\API\Integration;

use App\Http\Requests\API\Auth\LoginRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait CanLoginViaExternalAPI
{
    protected string $abilityName = '*';
    protected int $tokenLifetime  = 10;

    protected function handleLogin(LoginRequest $request): JsonResponse
    {
        $user = User::ofEmail($request->email)->first();

        if (is_null($user) || !Hash::check($request->password, $user->password)) {
            return $this->sendError(
                message: trans('api.auth.errors.incorrect_creds'),
                status: ResponseAlias::HTTP_UNAUTHORIZED
            );
        }

        if (!$user->status) {
            return $this->sendError(
                message: 'User has been suspended',
                status: ResponseAlias::HTTP_FORBIDDEN
            );
        }

        return $this->sendResponse(
            [
                'token' => $user->createToken(
                    Str::limit($request->userAgent() ?? 'unknown device', 120),
                    [$this->abilityName],
                    Carbon::now()->addMinutes($this->tokenLifetime)
                )->plainTextToken,
            ],
            trans('api.auth.success.login_success')
        );
    }
}

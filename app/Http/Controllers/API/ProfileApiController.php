<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Exceptions\PublicException;
use App\Http\Requests\API\Profile\DeleteUserRequest;
use App\Http\Requests\Profile\UserSettingsFormRequest;
use App\Http\Requests\UploadProfileImageRequest;
use App\Http\Resources\DietData;
use App\Http\Resources\Profile\User;
use App\Mail\MailMailable;
use App\Services\Users\UserAvatarService;
use App\Services\Users\UserProfileService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * User profile API controller.
 *
 * @package App\Http\Controllers\API
 */
final class ProfileApiController extends APIBase
{
    /**
     * Get foodpoints.
     *
     * @note endpoint is used outside of Sanctum guard
     */
    public function getFoodpoints(): JsonResponse
    {
        $result = [
            'auth'    => false,
            'balance' => null
        ];
        $message = 'User is not logged in.';

        if (\Auth::check()) {
            $result['auth']    = true;
            $result['balance'] = \Auth::user()->balance;
            $message           = 'User balance load successfully.';
        }

        return $this->sendResponse($result, $message);
    }

    /**
     * Get menu data.
     *
     * @note endpoint is used outside of Sanctum guard
     */
    public function getMenuData(): JsonResponse
    {
        $result = [
            'auth'      => false,
            'balance'   => null,
            'challenge' => null
        ];
        $message = 'User is not logged in.';

        if (\Auth::check()) {
            $user                = \Auth::user();
            $result['auth']      = true;
            $result['balance']   = $user->balance;
            $result['challenge'] = $user->getCourseData();
            $message             = 'Menu date load successfully.';
        }

        return $this->sendResponse($result, $message);
    }

    /**
     * Get user Diet data.
     *
     * @note endpoint is used outside of Sanctum guard
     */
    public function getDietData(): JsonResponse
    {
        $result = [
            'auth'     => false,
            'dietdata' => false,
        ];
        $message = 'User is not logged in.';

        if (\Auth::check()) {
            $result['auth']     = true;
            $user               = \Auth::user();
            $result['dietdata'] = empty($user?->dietdata) ?
                trans('common.empty_nutrients') :
                new DietData(collect($user->dietdata));
            $message = 'Success';
        }

        return $this->sendResponse($result, $message);
    }

    /**
     * Get profile data with diet data and subscriptions.
     *
     * @route POST /api/v1/profile/all
     */
    public function getProfileData(Request $request): JsonResponse
    {
        /**@var \App\Models\User|null $user */
        $user = $request->user();
        if (is_null($user)) {
            return $this->sendError(
                message: 'User is not logged in.',
                status: ResponseAlias::HTTP_UNAUTHORIZED
            );
        }

        $user->load(
            [
                'assignedChargebeeSubscriptions' => fn(HasMany $relation) => $relation->orderBy('data->status'),
                'subscriptions'
            ]
        );
        $user->main_goal = $user->latestQuestionnaire_goal;

        return $this->sendResponse(new User($user), trans('common.success'));
    }

    /**
     * Update user settings.
     */
    public function update(UserSettingsFormRequest $request, UserProfileService $service): JsonResponse
    {
        try {
            $service->processStore($request);
        } catch (PublicException $e) {
            return $this->sendError(
                message: $e->getMessage(),
                status: ResponseAlias::HTTP_BAD_REQUEST
            );
        }

        return $this->sendResponse(trans('common.information_updated', locale: $request->lang), trans('common.success'));
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(UploadProfileImageRequest $request, UserAvatarService $service): JsonResponse|RedirectResponse
    {
        return $service->processUpdate($request);
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(UploadProfileImageRequest $request, UserAvatarService $service): JsonResponse|RedirectResponse
    {
        return $service->processDelete($request);
    }

    /**
     * Get profile data with diet data and subscriptions.
     *
     * @route POST /api/v1/profile/delete
     */
    public function deleteProfile(DeleteUserRequest $request): JsonResponse
    {
        $user       = $request->user();
        $mailObject = new MailMailable(
            'emails.deleteUserFromMobileApp',
            [
                'client'    => $user,
                'timestamp' => $request->timestamp,
                'reason'    => $request->reason ?? trans('common.no_data'),
            ]
        );
        $mailObject->from(config('mail.from.address'), 'Foodpunk Portal')
            ->to(config('mail.from.address'))
            ->subject(trans('common.delete_client_request', locale: 'en'));

        Mail::queue($mailObject, 'emails');

        // Clear user tokens to ensure he is logged out completely.
        $user->tokens()->delete();

        return $this->sendResponse('OK', 'You can log out now');
    }
}

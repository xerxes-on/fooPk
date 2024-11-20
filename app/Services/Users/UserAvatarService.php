<?php

namespace App\Services\Users;

use App\Http\Requests\UploadProfileImageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class UserAvatarService
{
    /**
     * Process user avatar update.
     */
    public function processUpdate(UploadProfileImageRequest $request): JsonResponse|RedirectResponse
    {
        try {
            /**@var \App\Models\User $user */
            $user            = $request->user();
            $profileImageUrl = $user->uploadProfileImageFromRequest($request);
        } catch (\Exception $exception) {
            return $request->expectsJson() ?
                response()->json(
                    [
                        'success' => false,
                        'message' => trans('common.profile_image_uploaded_fail') . '. ' . $exception->getMessage()
                    ]
                ) :
                back()->withErrors(trans('common.profile_image_uploaded_fail'));
        }

        return $request->expectsJson() ?
            response()->json(
                [
                    'success'           => true,
                    'message'           => trans('common.profile_image_uploaded'),
                    'profile_image_url' => $profileImageUrl
                ]
            ) :
            back()->with(
                [
                    'message'           => __('common.profile_image_uploaded'),
                    'profile_image_url' => $profileImageUrl
                ]
            );
    }

    /**
     * Process user avatar delete.
     */
    public function processDelete(Request $request): JsonResponse|RedirectResponse
    {
        /**@var \App\Models\User $user */
        $user = $request->user();
        logger('delete image');
        $user->deleteProfileImage();
        return $request->expectsJson() ?
            response()->json(
                [
                    'success' => true,
                    'message' => __('common.success')
                ]
            ) :
            back()->with(['message' => __('common.success')]);
    }
}

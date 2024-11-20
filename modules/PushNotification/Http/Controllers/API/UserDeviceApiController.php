<?php

namespace Modules\PushNotification\Http\Controllers\API;

use App\Http\Controllers\API\APIBase;
use Illuminate\Http\JsonResponse;
use Modules\PushNotification\Http\Requests\API\RegisterUserDevice;
use Modules\PushNotification\Models\UserDevice as UserDeviceModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Controller representing user devices functionality.
 *
 * @package Modules\PushNotification\API
 */
final class UserDeviceApiController extends APIBase
{
    /**
     * Register new user device or update existing one.
     *
     * @route POST /api/user-device/register
     */
    public function register(RegisterUserDevice $request): JsonResponse
    {
        /**
         * Check whether the same device is already registered.
         * If so, we only need to update the record.
         * Token can vary every time app is reinstalled, so we need to update it.
         */
        $recordId = UserDeviceModel::where(
            [
                ['user_id', $request->user_id],
                ['fingerprint', $request->fingerprint],
            ]
        )
            ->pluck('id')
            ->first();
        if ($recordId) {
            UserDeviceModel::whereId($recordId)->update($request->validated());
            return $this->sendResponse(null, trans('common.information_updated'));
        }

        UserDeviceModel::create($request->validated());
        return $this->sendResponse(null, trans('common.success'), ResponseAlias::HTTP_CREATED);
    }
}

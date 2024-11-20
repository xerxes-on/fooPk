<?php

namespace Modules\Chargebee\Http\Controllers;

use App\Http\Controllers\API\APIBase;
use Exception;
use Modules\Chargebee\Jobs\ProcessChargebeeWebhook;
use Modules\Chargebee\Requests\API\ChargebeeWebHookRequest;
use Modules\Chargebee\Services\ChargebeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @package Modules\Chargebee\Http\Controllers\API
 */
class ChargebeeController extends APIBase
{

    /**
     * Update a subscription.
     */
    public function updateSubscriptionData(Request $request, ChargebeeService $service): JsonResponse
    {
        $user = $request->user();

        try {
            $service->refreshSubscriptionData($user);
        } catch (Exception $exception) {
            Log::error(
                'Subscription data update failed.',
                [
                    'user' => $user->only(['id', 'first_name', 'last_name', 'email']),
                    'error' => $exception->getMessage(),
                ]
            );

            return $this->sendError(null, __('common.subscription_data_update_failed'));
        }

        $user->load(
            [
                'assignedChargebeeSubscriptions' => function ($q) {
                    $q->orderBy('data->status');
                }
            ]
        );
        return $this->sendResponse($user, __('common.subscription_data_update_success'));
    }
}

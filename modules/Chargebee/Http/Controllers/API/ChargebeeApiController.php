<?php

namespace Modules\Chargebee\Http\Controllers\API;

use App\Http\Controllers\API\APIBase;
use ChargeBee\ChargeBee\Models\Subscription as ChargeBee_Subscription;
use ChargeBee\ChargeBee\Models\Customer as ChargeBee_Customer;
use Exception;
use Modules\Chargebee\Jobs\ProcessChargebeeWebhook;
use Modules\Chargebee\Requests\API\ChargebeeWebHookRequest;
use Modules\Chargebee\Services\ChargebeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User as UserModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @package Modules\Chargebee\Http\Controllers\API
 */
class ChargebeeApiController extends APIBase
{

    private array $webhookMap;

    /**
     * Webhook from Chargebee
     * TODO:: implement api_token for security
     */
    public function webhook(ChargebeeWebHookRequest $request): JsonResponse
    {
        // TODO:: check sign
        //handle events
        try {
            $this->handleWebhook($request->all());
        } catch (Exception $exception) {
            Log::error('Chargebee webhook handle failed. ' . $exception->getMessage());
        }

        return $this->sendResponse(null, trans('api.chargebee_sync_success'));
    }

    public function handleWebhook($requestData): void
    {

        $eventType = data_get($requestData, 'event_type');
        $jobDelay = $this->getJobDelay($eventType);

        // TODO:: remove after release to production
        if (!app()->environment('local')) {
            ProcessChargebeeWebhook::dispatch($requestData)->delay($jobDelay);
        } else {
            ProcessChargebeeWebhook::dispatchSync($requestData);
        }
    }

    public function getJobDelay($event): int
    {
        $this->webhookMap = config('chargebee.handlers');
        return $this->webhookMap[$event]['delay'] ?? 0;
    }

    /**
     * By subscription ID change user email in Chargebee, allow only if Chargebee user has not email, or email is not valid
     * @param Request $request
     * @return JsonResponse
     * @throws \Modules\Chargebee\Exceptions\ChargebeeConfigurationFailure
     */

    public function attachSubscriptionToUser(Request $request)
    {

        // TODO:: to think about security and how to restart event with subscription creation

        $subscriptionId = $request->get('subscriptionId');
        $userEmail = $request->get('userEmail');
        if (empty($userEmail) || empty($subscriptionId)) {
            return $this->sendError(null, trans('common.unexpected_error'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }



        app(ChargebeeService::class)->configureEnvironment();

        $subscription = ChargeBee_Subscription::retrieve($subscriptionId);

        $requestUserEmailUpdated = false;
        if ($subscription?->customer()?->id) {
            $customerRequest = ChargeBee_Customer::retrieve($subscription->customer()->id);

            if ($userEmail && $customerRequest && $customerRequest->customer() && !empty($customerRequest->customer()?->id)) {
                $userExistingEmail = $customerRequest->customer()?->email;
                if (!filter_var($userExistingEmail, FILTER_VALIDATE_EMAIL)) {
                    $requestUserEmailUpdated = ChargeBee_Customer::update($customerRequest->customer()->id, [
                        'email' => $userEmail,
                    ]);
                } else {
                    return $this->sendError(null, 'user already has real email', ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        }
        if ($requestUserEmailUpdated) {
            return $this->sendResponse(null, trans('api.success'));
        } else {
            return $this->sendError(null, trans('common.failed'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}

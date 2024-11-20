<?php

namespace Modules\Chargebee\Http\Controllers\API;

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
        if (!app()->environment('local')){
            ProcessChargebeeWebhook::dispatch($requestData)->delay($jobDelay);
        }
        else{
            ProcessChargebeeWebhook::dispatchSync($requestData);
        }
    }

    public function getJobDelay($event): int
    {
        $this->webhookMap = config('chargebee.handlers');
        return $this->webhookMap[$event]['delay'] ?? 0;
    }
}

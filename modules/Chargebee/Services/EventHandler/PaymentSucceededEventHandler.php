<?php

namespace Modules\Chargebee\Services\EventHandler;

use App\Models\User;
use Exception;
use Modules\Chargebee\Services\ChargebeeService;

/**
 * Uses only for sync foodpoints to user
 **/
class PaymentSucceededEventHandler extends BaseEventHandler
{

    public function handle()
    {

        $foodpointsConfig = config('chargebee.foodpoints');
        $lineItems = data_get($this->eventData, 'content.invoice.line_items');

        // checking if invoice has lineItems with foodpoints
        $isFoodpointsPayment = false;
        if (!empty($lineItems) && is_array($lineItems)) {
            foreach ($lineItems as $line_item) {
                if (isset($line_item['entity_id'])) {
                    $entityId = ChargebeeService::removeCurrencyFromChargebeePlanId($line_item['entity_id']);
                    if (isset($foodpointsConfig[$entityId])) {
                        $isFoodpointsPayment = true;
                        break;
                    }
                }
            }
        }
        $customerEmail = data_get($this->eventData, 'content.customer.billing_address.email');
        if (empty($customerEmail)) $customerEmail = data_get($this->eventData, 'content.customer.email');
        if (empty($customerEmail)) {
            throw new Exception('Chargebee event failed, '.self::class.' empty email, eventId:' . $this->eventData['id'] . ' transactionID:' . $this->eventData['content']['transaction']['id']);
        }
        if ($isFoodpointsPayment) {
            $user = User::ofEmail($customerEmail)->orderBy('status', 'DESC')->first();
            if ($user) $this->service->syncUserFoodpointsInvoices($user);
        }
    }
}
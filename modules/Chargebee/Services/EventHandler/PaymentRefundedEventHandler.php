<?php

namespace Modules\Chargebee\Services\EventHandler;

use Exception;
use Modules\Chargebee\Services\ChargebeeService;

/**
 * Uses only for remove foodpoints to user when payment refunded
 **/
class PaymentRefundedEventHandler extends BaseEventHandler
{

    public function handle()
    {

        $foodpointsConfig = config('chargebee.foodpoints');
        $lineItems = data_get($this->eventData, 'content.invoice.line_items');

        // checking if invoice has lineItems with foodpoints
        $isFoodpointsPayment = false;
        $entityId = null;
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
        $customerEmail = data_get($this->eventData, 'content.customer.email');
        if (empty($customerEmail)) $customerEmail = data_get($this->eventData, 'content.billing_address.email');

        if (empty($customerEmail)) {
            throw new Exception('Chargebee event failed, '.self::class.' empty email, eventId:' . $this->eventData['id'] . ' transactionID:' . $this->eventData['content']['transaction']['id']);
        }

        if ($isFoodpointsPayment) {
            $amount = intval($foodpointsConfig[$entityId]);
            $invoiceId = $this->eventData['content']['invoice']['id'];
            if ($amount > 0) $this->service->withdrawFoodpoints($invoiceId);
        }
    }
}
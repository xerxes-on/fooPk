<?php

namespace Modules\Chargebee\Services;

use App\Enums\Admin\Permission\RoleEnum;
use App\Enums\ChargeBeeSubscriptionStatusEnum;
use App\Enums\User\UserStatusEnum;
use App\Helpers\CacheKeys;
use App\Helpers\Calculation;
use App\Mail\MailMailable;
use App\Models\User;
use App\Notifications\UserHasTwoActiveChargebeeSubscriptionsNotification;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Carbon\Carbon;
use ChargeBee\ChargeBee\Environment as ChargeBee_Environment;
use ChargeBee\ChargeBee\Models\Customer as ChargeBee_Customer;
use ChargeBee\ChargeBee\Models\Estimate as ChargeBee_Estimate;
use ChargeBee\ChargeBee\Models\Invoice as ChargeBee_Invoice;
use ChargeBee\ChargeBee\Models\Plan as ChargeBee_Plan;
use ChargeBee\ChargeBee\Models\Subscription as ChargeBee_Subscription;
use Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Mail;
use Modules\Chargebee\Enums\CurrenciesEnum;
use Modules\Chargebee\Exceptions\ChargebeeConfigurationFailure;
use Modules\Chargebee\Exceptions\ChargebeeEventFailed;
use Modules\Chargebee\Models\ChargebeeInvoice as ChargebeeInvoiceModel;
use Modules\Chargebee\Models\ChargebeeSubscription;
use Modules\Course\Enums\CourseId;
use Password;
use Throwable;

/**
 * TODO: refactor to simplify. Another god class is bad for mental health...
 * TODO: class has 1059 lines of code.  Avoid really long classes.
 * TODO: class has 11 public methods. Consider refactoring ChargebeeService to keep number of public methods under 10.
 * TODO: class has a coupling between objects value of 31. Consider to reduce the number of dependencies under 13.
 * TODO: class has an overall complexity of 169 which is very high.
 * TODO: Typehint missing
 */
class ChargebeeService
{
    private array $webhooksHandlersMap;

    /**
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function syncUserFoodpointsInvoices(User $user)
    {
        $userIds = [$user->id];

        if ($user->assignedChargebeeSubscriptions->count()) {
            //get chargebee ids from users from which assigned subscriptions
            $userIds = array_unique(array_merge($userIds, $user->assignedChargebeeSubscriptions->pluck('user_id')->toArray()));
        }
        $foodpointsConfig = config('chargebee.foodpoints');

        User::whereIn('id', $userIds)
            ->each(
                function ($user) use ($foodpointsConfig) {
                    try {
                        $chargebeeIds = $this->getChargebeeCustomerIds($user);

                    } catch (\Exception $exception) {
                        Log::error('Could not sync chargebee data for user ' . $user->id . '. ' . $exception->getMessage());
                    }

                    if (isset($chargebeeIds) && !empty($chargebeeIds) && is_array($chargebeeIds)) {
                        try {
                            DB::transaction(
                                function () use ($chargebeeIds, $user, $foodpointsConfig) {
                                    $invoicesRaw = ChargeBee_Invoice::all(
                                        array(
                                            "sortBy[asc]"    => "updated_at",
                                            "customerId[in]" => $chargebeeIds,
                                            'status'         => ChargebeeInvoiceModel::STATUS_PAID,
                                            'limit'          => 100,
                                        )
                                    );

                                    $invoicesArray = [];
                                    // foodpoints related invoices
                                    foreach ($invoicesRaw as $entry) {
                                        $invoice = $entry->invoice()->getValues();
                                        if (isset($invoice['line_items'])) {
                                            foreach ($invoice['line_items'] as $line_item) {
                                                if (isset($line_item['entity_id'])) {
                                                    $entityId = ChargebeeService::removeCurrencyFromChargebeePlanId($line_item['entity_id']);
                                                    if (isset($foodpointsConfig[$entityId])) {
                                                        $invoicesArray[$invoice['id']] = $invoice;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $invoiceIds = array_keys($invoicesArray);
                                    $invoiceDb  = ChargebeeInvoiceModel::whereIn('invoice_id', $invoiceIds)->get()->keyBy(
                                        'invoice_id'
                                    )->toArray();

                                    foreach ($invoicesArray as $invoiceId => $invoice) {
                                        if ($invoice['status'] == ChargebeeInvoiceModel::STATUS_PAID) {
                                            if (!isset($invoiceDb[$invoiceId]) || (isset($invoiceDb[$invoiceId]) && $invoiceDb[$invoiceId]['processed'] == ChargebeeInvoiceModel::NOT_PROCESSED)) {
                                                $record = [
                                                    'user_id'          => $user->id,
                                                    'assigned_user_id' => $user->id,
                                                    'data'             => $invoice,
                                                    'invoice_id'       => $invoiceId,
                                                    'invoice_date'     => intval($invoice['date']),
                                                    'customer_id'      => $invoice['customer_id'],
                                                    'status'           => $invoice['status'],
                                                    'processed'        => ChargebeeInvoiceModel::NOT_PROCESSED
                                                ];


                                                $invoiceRecord = ChargebeeInvoiceModel::updateOrCreate(
                                                    ['invoice_id' => $invoiceId],
                                                    $record
                                                );

                                                $amount = 0;
                                                if (isset($invoice['line_items'])) {
                                                    foreach ($invoice['line_items'] as $line_item) {
                                                        if (isset($line_item['entity_id'])) {
                                                            $entityId = ChargebeeService::removeCurrencyFromChargebeePlanId($line_item['entity_id']);
                                                            if (isset($foodpointsConfig[$entityId])) {
                                                                $amount = (int)$foodpointsConfig[$entityId];
                                                            }
                                                        }
                                                    }
                                                }

                                                // TODO:: refactor as standalone method
                                                if ($amount > 0) {
                                                    # set user balance
                                                    try {
                                                        $user->deposit(
                                                            $amount,
                                                            [
                                                                'description' => "Deposit of $amount FoodPoints based on chargebee invoice: $invoiceId date:" . Carbon::createFromTimestamp(
                                                                    $record['invoice_date']
                                                                )->format('Y-m-d H:i:s')
                                                            ]
                                                        );
                                                    } catch (ExceptionInterface $e) {
                                                        logError($e);
                                                        return response()->json(['success' => false]);
                                                    }
                                                }

                                                $invoiceRecord->processed = ChargebeeInvoiceModel::PROCESSED;
                                                $invoiceRecord->save();
                                            }
                                        }
                                    }
                                },
                                5
                            );
                        } catch (Throwable $e) {
                            logError($e);
                        }
                    }
                    return true;
                }
            );
        return true;
    }

    public static function getChargebeePlanIdFromSubscriptionData(array $subscriptionData)
    {
        $chargebeePlanId = null;
        // TODO:: review that, because $subscriptionData['plan_id'] is for ProductCatalog v1
        if (!empty($subscriptionData) && !empty($subscriptionData['plan_id'])) {
            $chargebeePlanId = $subscriptionData['plan_id'];

        } elseif (!empty($subscriptionData['subscription_items'])) {
            foreach ($subscriptionData['subscription_items'] as $item) {
                if ($item['item_type'] == 'plan' && $item['object'] == 'subscription_item') {
                    $chargebeePlanId = $item['item_price_id'];
                }
            }
        }
        if ($chargebeePlanId) {
            $chargebeePlanId = ChargebeeService::prepareChargebeePlanId($chargebeePlanId);
        }
        return $chargebeePlanId;
    }

    public static function removeCurrencyFromChargebeePlanId($planId)
    {
        // check if last 4 item is '-', try to remove it
        $strlen = strlen($planId);

        if ($strlen > 4 && $planId[$strlen - 4] == '-') {
            $currency = strtolower(substr($planId, $strlen - 3, 3));
            if (in_array($currency, CurrenciesEnum::values())) {
                $planId = substr($planId, 0, $strlen - 4);
            }
        }
        return $planId;
    }

    public static function getChargebeeChallengesConfig()
    {
        $result = [];
        $config = config('chargebee.challenges_config');
        if (!empty($config) && is_array($config)) {
            // change keys to lower case
            $result = array_change_key_case($config);
            if (isset($result['langs'])) {
                foreach ($result['langs'] as $lang => $values) {
                    $result['langs'][$lang] = array_change_key_case($values);
                }
            }
        }
        return $result;
    }

    /**
     * Return AboChallenge ID based on chargebee plan ID (string)
     *
     * @param string $planId
     * @param string $lang
     * @return bool/int
     */
    public static function issetChallengeIdByChargebeePlanId($planId = null, $lang = 'general')
    {
        $result           = false;
        $challengesConfig = self::getChargebeeChallengesConfig();

        if (!empty($planId)) {
            $planId = self::prepareChargebeePlanId($planId);
            if (!empty($challengesConfig)) {
                if (!empty($challengesConfig['langs'][$lang][$planId])) {
                    $result = $challengesConfig['langs'][$lang][$planId];
                } elseif (!empty($challengesConfig[$planId])) {
                    $result = $challengesConfig[$planId];
                }
            }
        }

        return $result;
    }

    public static function prepareChargebeePlanId($planId)
    {
        if (is_string($planId)) {
            $trimmedPlanId = strtolower(trim($planId));
            return self::removeCurrencyFromChargebeePlanId($trimmedPlanId);
        }

        return $planId;
    }

    /**
     * @throws ChargebeeEventFailed
     * @throws \Exception
     */
    public function handleSubscriptionCreated($eventData)
    {
        if (
            empty($eventData['content']) ||
            empty($eventData['content']['subscription']) ||
            empty($eventData['content']['subscription']['id'])
        ) {
            throw new ChargebeeEventFailed('Chargebee event failed, empty $subscriptionId');
        }

        $subscriptionId = $eventData['content']['subscription']['id'];

        if (empty($subscriptionId)) {
            $exceptionText = 'Chargebee event failed, empty $subscriptionId';
            throw new ChargebeeEventFailed($exceptionText);
        }

        $customerId = $eventData['content']['subscription']['customer_id'] ?? $eventData['content']['customer']['id'] ?? 0;

        if (empty($customerId)) {
            Log::error('Chargebee event failed, empty $customerId $requestData=' . var_export($eventData, true));
        }

        $userEmail = data_get($eventData, 'content.customer.email');
        if (empty($userEmail)) {
            $userEmail = data_get($eventData, 'content.billing_address.email');
        }
        if (empty($userEmail)) {
            $userEmail = data_get($eventData, 'content.subscription.customer_id');
        }

        $planId = self::getChargebeePlanIdFromSubscriptionData($eventData['content']['subscription']);

        // TODO:: refactor it
        $order = [
            'id'          => $subscriptionId,
            'email'       => $userEmail,
            'order_id'    => $eventData['content']['subscription']['id'],
            'plan_id'     => $planId,
            'customer_id' => $customerId,
            'first_name'  => (!empty($eventData['content']['customer']['billing_address']['first_name'])) ? $eventData['content']['customer']['billing_address']['first_name'] : '',
            'last_name'   => (!empty($eventData['content']['customer']['billing_address']['last_name'])) ? $eventData['content']['customer']['billing_address']['last_name'] : '',
            'eventData'   => $eventData,
        ];
        $customerEmail = trim(strtolower($order['email']));

        $_user                               = User::ofEmail($customerEmail)->orderBy('status', 'DESC')->first();
        $chargebeeSubscriptionWithSamePlanID = false;
        if ($_user) {
            $planId = $order['plan_id'];

            $subscriptions = $_user->assignedChargebeeSubscriptions()->get();
            foreach ($subscriptions as $subscription) {
                if ($chargebeeSubscriptionWithSamePlanID) {
                    break;
                }


                // TODO:: please review that @NickMost, do we need that still?
                $planIdOld = self::getChargebeePlanIdFromSubscriptionData($subscription->data);
                if ($planIdOld == $planId) {
                    $chargebeeSubscriptionWithSamePlanID = true;
                }
            }
        }

        $msg = 'ChargeBee import #' . $subscriptionId . ', plan_id=' . $order['plan_id'] . ' processing!';
        Log::info($msg);
        // checking if plan_id is in available in creation list
        $userCreationPlans      = config('chargebee.create_user_plan');
        $userNotificationsPlans = config('chargebee.create_silent_plan');
        $notificationsEmails    = config('chargebee.adminNotificationEmails');
        $trimmedChargebeePlanId = self::prepareChargebeePlanId($order['plan_id']);
        if (
            (!empty($userCreationPlans) && in_array($trimmedChargebeePlanId, $userCreationPlans)) ||
            (
                (
                    empty($_user)
                    &&
                    self::issetChallengeIdByChargebeePlanId($trimmedChargebeePlanId) !== false
                )
                ||
                (
                    !empty($_user)
                    &&
                    self::issetChallengeIdByChargebeePlanId($trimmedChargebeePlanId, $_user->lang) !== false
                )
            )
        ) {
            $existUser    = User::ofEmail($customerEmail)->exists();
            $customerData = [
                'email'      => $customerEmail,
                'first_name' => $order['first_name'],
                'last_name'  => $order['last_name'],
            ];

            $customerData['chargebee_id'] = $subscriptionId;

            $_user       = $this->getOrCreateUser($customerData);
            $challengeId = self::getChallengeIdByChargebeePlanId($trimmedChargebeePlanId, $_user->lang);


            // probably could be place of issue with double subscriptions
            // updating data from chargebee for the current user, whenever user not exists before, we need to sync his transactions
            $this->refreshSubscriptionData($_user); // todo: unhandled exception

            // TODO:: review challenges @NickMost???
            if (!$existUser) {

                # create subscription
                $_user->maybeCreateSubscription();

                $_user->status = true;
                $_user->save();

                # generate token from reset password
                $token = Password::getRepository()->create($_user);

                # send email
                $mailObject = new MailMailable('emails.welcome', ['client' => $_user, 'token' => $token]);
                $mailObject
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($_user->email)
                    ->bcc(config('mail.from.address'))
                    ->subject('Los geht’s! / Let’s go!')
                    ->onQueue('emails');
                Mail::queue($mailObject);


                $msg = "ChargeBee import #$subscriptionId created new user account, plan_id={$order['plan_id']} USER_ID=$_user->id";
                Log::info($msg);
            } else {
                // TODO:: to think about welcome email in this case, does it need to be sent?

                // the same function called in $_user
//                $this->refreshSubs2criptionData($_user);
//                $this->checkAndCreateInternalSubscription($_user);

                $_user->status = true;
                $_user->save();
            }


            $_user->setFirstTimeCourse();

            $_user->addCourseIfNotExists($challengeId);

            // TODO:: please review that @NickMost, do we need that still?
            if ($existUser && !$chargebeeSubscriptionWithSamePlanID) {
                // user hasn't subscription with the same UUID, it's reactivation or activation for user who hasn't before subscription
                // reactivation notification

                $msg = 'ChargeBee import #' . $subscriptionId . ' user account already exists, plan_id=' . $order['plan_id'] . ' USER_ID=' . $_user->id;
                Log::info($msg);

                $to = $notificationsEmails;
                # send email
                $mailObject = new MailMailable('emails.importAccountAlreadyExists', ['order' => $order]);

                $mailObject->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($to)
                    ->subject('Chargebee order, account duplication founded.')
                    ->onQueue('emails');

                Mail::queue($mailObject);
            }

            // TODO:: need to generate meal plan?

        } elseif (!empty($userNotificationsPlans) && (in_array($trimmedChargebeePlanId, $userNotificationsPlans))) {
            $msg = 'ChargeBee import #' . $subscriptionId . ' no need to create , plan_id=' . $order['plan_id'];
            Log::info($msg);
            $to = $notificationsEmails;

            # send email
            $mailObject = new MailMailable('emails.importNoNeedToCreateUserAccount', ['order' => $order]);
            $mailObject->from(config('mail.from.address'), config('mail.from.name'))
                ->to($to)
                ->subject('Chargebee order processed success, no need to create account.')
                ->onQueue('emails');

            Mail::queue($mailObject);
        }

        $msg = 'ChargeBee import #' . $subscriptionId . ' done!';
        Log::info($msg);
    }

    /**
     * Create first time user or get from database
     *
     * TODO:: move into UserService
     * @param array $customerData
     * @return User
     */
    private function getOrCreateUser(array $customerData): User
    {
        $userData = [
            'first_name' => $customerData['first_name'],
            'last_name'  => $customerData['last_name'],
            'status'     => UserStatusEnum::ACTIVE->value,
            'password'   => Hash::make('8Y5jXLBpi4vj'),
        ];
        if (!empty($customerData['chargebee_id'])) {
            $userData['chargebee_id'] = $customerData['chargebee_id'];
        }

        $user = User::firstOrCreate(
            ['email' => $customerData['email']],
            $userData
        );

        if (!$user->hasRole(RoleEnum::USER->value)) {
            $user->assignRole(RoleEnum::USER->value);
        }

        # ATTENTION --> refresh model to load attributes with default values
        $user = $user->fresh();

        # check empty dietData
        if (empty($user->dietdata) && $user->isQuestionnaireExist() && $dietData = Calculation::calcUserNutrients($user->id)) {
            $user->dietdata = $dietData;
            $user->save();
        }

        return $user;
    }

    //--== HOOK HANDLERS ==--

    /**
     * Internal method, set First time challenge,


    //--== END HOOK HANDLERS ==--

    /**
     * @throws \Exception
     */
    public function refreshSubscriptionData(User $user): bool
    {
        $this->configureEnvironment();

        return $this->refreshUserSubscriptionData($user);
    }

    /**
     * Configure chargebee API access credentials
     * @throws ChargebeeConfigurationFailure
     */
    public function configureEnvironment(): void
    {
        if (config('app.chargebee.site') && config('app.chargebee.auth_user')) {
            ChargeBee_Environment::configure(config('app.chargebee.site'), config('app.chargebee.auth_user'));
            return;
        }
        \Log::error('Chargebee API access credentials does not configured');
        throw new ChargebeeConfigurationFailure('Chargebee API access credentials does not configured');
    }

    /**
     * @param User $user
     * @return boolean
     * @throws \Exception
     */
    public function refreshUserSubscriptionData(User $user)
    {
        //we should take id for user for which refresh action fired and ids of users from which chargebee subscriptions was assigned ( if was assigned )
        //case: user have two active subscription in chragebee under own email. he shared one of them to family member who doesn't have own chargebee account
        //to be able to update such "shared" subscription status we need to use particular subscription id or update subscription by user's credentials who shared
        // subscription

        $userIds = [$user->id];

        if ($user->assignedChargebeeSubscriptions->count()) {
            //get chargebee ids from users from which assigned subscriptions
            $userIds = array_unique(array_merge($userIds, $user->assignedChargebeeSubscriptions->pluck('user_id')->toArray()));
        }

        User::whereIn('id', $userIds)
            ->each(
                function ($user) {
                    try {
                        $chargebeeIds = $this->getChargebeeCustomerIds($user);
                    } catch (\Exception $exception) {
                        Log::error('Could not sync chargebee data for user ' . $user->id . '. ' . $exception->getMessage());
                    }

                    if (!empty($chargebeeIds) && is_array($chargebeeIds)) {
                        try {
                            DB::transaction(
                                function () use ($chargebeeIds, $user) {
                                    $subscriptions = ChargeBee_Subscription::all(
                                        array(
                                            "sortBy[asc]"    => "updated_at",
                                            "customerId[in]" => $chargebeeIds,
                                            'limit'          => 100,
                                        )
                                    );
                                    if (!is_null($subscriptions)) {
                                        $this->updateUserSubscriptions($user, $subscriptions);
                                    }
                                },
                                5
                            );
                        } catch (Throwable $e) {
                            logError($e);
                        }
                    }

//                    return true;
                }
            );

        $this->checkAndCreateInternalSubscription($user);
        return true;
    }

    /**
     * Check chargebee id and try to fetch it from service if required
     * @return array
     * @throws \Exception
     */
    private function getChargebeeCustomerIds(User $user)
    {
        try {
            $all = ChargeBee_Customer::all(
                [
                    "email[is]" => $user->email,
                    "limit"     => 100,
                ]
            );

            $customers = [];
            foreach ($all as $entry) {
                $customers[] = $entry->customer();
            }

            $chargebeeCustomerIds = [];
            if (!empty($customers)) {
                $chargebeeCustomerIds = array_column($customers, 'id');
            } else {
                \Log::debug('No chargebee customer account for userID: ' . $user->id);
            }

            return $chargebeeCustomerIds;
        } catch (\Exception $exception) {
            logError($exception);
            throw $exception;
        }
    }

    /**
     * Update existed or create new subscription records, remove old ones
     * @param User $user
     * @param $subscriptions
     * @param $silence
     */
    public function updateUserSubscriptions(User $user, $subscriptions = null, $silence = false)
    {
        if (!is_null($subscriptions)) {
            $subscriptions = $this->prepareSubscriptionDataFromChargebee($subscriptions);

            $newSubscriptions = [];
            foreach ($subscriptions as $subscriptionData) {
                if ($subscriptionObject = ChargebeeSubscription::query()->updateOrCreate(
                    ['uuid' => $subscriptionData['id'], 'user_id' => $user->id],
                    ['data' => $subscriptionData]
                )) {
                    //set or update payment_method if presented
                    if (!empty($subscriptionData['payment_method'])) {
                        $subscriptionObject->payment_method = data_get($subscriptionData, 'payment_method');
                        $subscriptionObject->save();
                    }

                    if ($subscriptionObject->wasRecentlyCreated) {
                        $newSubscriptions[] = $subscriptionObject;
                    }
                }
            }

            if (!empty($newSubscriptions)) {
                \DB::table('chargebee_subscriptions')
                    ->whereIn('id', array_column($newSubscriptions, 'id'))
                    ->update(['assigned_user_id' => $user->id]);
            }

            //remove from db subscription which not presented in the chargebee
            // TODO:: temporary dissabled, need to be investigated
//            ChargebeeSubscription::query()
//                ->where('user_id', $user->id)
//                ->whereNotIn(
//                    'uuid',
//                    array_column($subscriptions, 'id')
//                )
//                ->delete();

            //send notification to admins if user has two active subscription assigned to him
            if (!$silence) {
                $this->notifyAdminsAboutTwoActiveSubscriptionsIfRequired($user);
            }
        }
    }

    private function prepareSubscriptionDataFromChargebee($rawSubscriptions)
    {
        $subscriptions = [];
        foreach ($rawSubscriptions as $entry) {
            $subscription                            = $entry->subscription();
            $subscriptionDataArray                   = $subscription->getValues();
            $customer                                = $entry->customer()->getValues();
            $paymentMethod                           = data_get($customer, 'payment_method');
            $subscriptionDataArray['payment_method'] = $paymentMethod;
            $subscriptionStatus                      = data_get(
                $subscriptionDataArray,
                'status'
            );

            if ($subscriptionStatus && in_array(
                $subscriptionStatus,
                ChargeBeeSubscriptionStatusEnum::renewableStatus()
            )) {
                if ($renewalEstimateData = $this->getSubscriptionRenewalEstimate(
                    data_get(
                        $subscriptionDataArray,
                        'id'
                    )
                )) {
                    if ($nextBillingTotalCents = data_get(
                        $renewalEstimateData,
                        'invoice_estimate.total'
                    )) {
                        $subscriptionDataArray[ChargebeeSubscription::NEXT_BILLING_AMOUNT_KEY] = $nextBillingTotalCents;
                    }
                }
            }

            $subscriptions[] = $subscriptionDataArray;
        }

        if (!empty($subscriptions)) {
            //prepare data before saving
            $subscriptions = array_map(
                function ($subscriptionData) {
                    return $this->prepareRawSubscriptionData(
                        $subscriptionData
                    );//TODO: kutas move to ChargebeeSubscription mutator

                },
                $subscriptions
            );
        }


        return $subscriptions;
    }

    /**
     * Fetch next billing amount and save to the subscription data
     * @param string|null $subscriptionChargebeeId
     * @return array|boolean
     */
    public function getSubscriptionRenewalEstimate(?string $subscriptionChargebeeId = null)
    {
        if ($subscriptionChargebeeId) {
            try {
                $result   = ChargeBee_Estimate::renewalEstimate($subscriptionChargebeeId);
                $estimate = $result->estimate();
                return $estimate->getValues();
            } catch (\Exception $e) {
                logError($e);
                return false;
            }
        }

        return false;
    }

    /**
     * @param array $subscriptionRawData Raw subscription data from Chargebee API
     * @return mixed
     */
    private function prepareRawSubscriptionData($subscriptionRawData)
    {
        //		$plans = Cache::get(CacheKeys::chargebeePlans());
        //		if (empty($plans)) {
        //			try {
        //				$plans = app(ChargebeeService::class)->getPlans();
        //				Cache::put(CacheKeys::chargebeePlans(), $plans, config('cache.lifetime_long'));
        //			} catch (\Exception $e) {
        //				logError($e);
        //				$plans = [];
        //			}
        //		}
        //
        //		$plans = collect($plans);
        //
        //		//add plan descriptions
        //		$subscriptionRawData['plan'] = $plans->firstWhere('id', data_get($subscriptionRawData, 'plan_id'));
        //		$subscriptionRawData['plan'] = data_get($subscriptionRawData, 'plan_id');

        //format timestamps to date string
        foreach (
            [
                'cancelled_at',
                'activated_at',
                'next_billing_at',
                'start_date',
                'cancel_schedule_created_at',
                'started_at'
            ] as $key
        ) {
            if ($value = ($subscriptionRawData[$key] ?? false)) {
                $subscriptionRawData[$key] = Carbon::createFromTimestamp($value)->format('d.m.Y');
            }
        }

        //format coins
        foreach (['mrr', 'next_billing_amount'] as $key) {
            if ($value = ($subscriptionRawData[$key] ?? false)) {
                $subscriptionRawData[$key] = $value / 100;
            }
        }

        return $subscriptionRawData;
    }

    /**
     * @param User $user
     */
    private function notifyAdminsAboutTwoActiveSubscriptionsIfRequired(User $user)
    {
        if (
            ($user->assignedChargebeeSubscriptions()
                    ->where(
                        function ($query) {
                            $query->where('data->status', 'active')->orWhere('data->status', 'in_trial');
                        }
                    )
                    ->count() > 1) && !Cache::has(CacheKeys::adminNotificationOnSecondChargebeeSubscription($user->id))
        ) {
            Notification::route('mail', config('mail.from.address'))
                ->notify(new UserHasTwoActiveChargebeeSubscriptionsNotification($user));
            Cache::put(CacheKeys::adminNotificationOnSecondChargebeeSubscription($user->id), 1, config('cache.lifetime_day'));
        }
    }

    protected function checkAndCreateInternalSubscription($user)
    {
        if (empty($user->subscription)) {
            // TODO:: check if subscription exists but not exists in user's scope - then create
            $chargebeePlanId = $user->getLastChargebeePlanId();
            if (empty($chargebeePlanId)) {
                return;
            }
            $challengeId = self::getChallengeIdByChargebeePlanId($chargebeePlanId, $user->lang);
            $user->addCourseIfNotExists($challengeId);

            $user->maybeCreateSubscription();
        }
    }

    /**
     * Handle chargebee subscription_cancelled event
     * @param $eventData
     */
    public function handleSubscriptionCanceled($eventData)
    {
        $subscriptionChargebeeId = data_get($eventData, 'content.subscription.id');

        $customerEmail = data_get($eventData, 'content.customer.email');
        if (empty($customerEmail)) {
            $customerEmail = data_get($eventData, 'content.billing_address.email');
        }
        if (empty($customerEmail)) {
            $customerEmail = data_get($eventData, 'content.subscription.customer_id');
        }


        if (!empty($customerEmail) && $user = User::ofEmail($customerEmail)->first()) {
            $now                              = Carbon::now();
            $subscriptionCancelledAtTimeStamp = data_get($eventData, 'content.subscription.cancelled_at');
            $subscriptionCancelledAt          = Carbon::createFromTimestamp($subscriptionCancelledAtTimeStamp);
            $subscriptions                    = $user->assignedChargebeeSubscriptions;
            $hasActiveSubscription            = false;

            if (!$subscriptions->isEmpty()) {
                $subscriptions = $subscriptions
                    ->filter(
                        fn($item) => (
                            in_array(
                                $item->data['status'],
                                ChargeBeeSubscriptionStatusEnum::potentiallyActiveStatus()
                            ) && $item->data['id'] != $subscriptionChargebeeId
                        )
                    )
                    ->map(
                        function ($item) {
                            $item->started_at   = null;
                            $item->cancelled_at = null;
                            if (!empty($item->data['started_at'])) {
                                $item->started_at = Carbon::parse($item->data['started_at']);
                            }
                            if (!empty($item->data['cancelled_at'])) {
                                $item->cancelled_at = Carbon::parse($item->data['cancelled_at']);
                            }
                            return $item;
                        }
                    )
                    // TODO:: review, could be possible issue
                    ->sortBy('started_at')
                    ->toArray();

                $hasActiveSubscription = !empty($subscriptions);
                foreach ($subscriptions as $subscription) {
                    if (!empty($subscription['cancelled_at']) && $subscription['cancelled_at'] < $now) {
                        $hasActiveSubscription = false;
                    }
                }
                // user hasn't active subscription, need to cancel
            }

            if (!$hasActiveSubscription) {
                $user->subscriptions()->where('active', true)->update(['ends_at' => $subscriptionCancelledAt]);
            }

            $this->syncSubscriptionsData($eventData, $user);
        }
    }

    /**
     * Uses for sync chargebee subscriptions data from chargebee into local database
     *
     * @param $eventData
     * @param  ?User $user
     */
    public function syncSubscriptionsData($eventData, ?User $user = null)
    {
        $customerEmail = '';
        //fetch
        $subscription            = data_get($eventData, 'content.subscription');
        $subscriptionChargebeeId = data_get($eventData, 'content.subscription.id');

        $customerChargebeeId = data_get($eventData, 'content.customer.id');
        $paymentMethod       = data_get($eventData, 'content.customer.payment_method');
        $subscriptionStatus  = data_get($eventData, 'content.subscription.status');

        if (empty($user)) {

            $customerEmail = data_get($eventData, 'content.customer.email');
            if (empty($customerEmail)) {
                $customerEmail = data_get($eventData, 'content.billing_address.email');
            }
            if (empty($customerEmail)) {
                $customerEmail = data_get($eventData, 'content.subscription.customer_id');
            }

            $user = !empty($customerEmail) ? User::ofEmail($customerEmail)->first() : null;
        }

        if (!empty($user)) {
            /** @var User $user */
            $user->update(['chargebee_id' => $customerChargebeeId]);

            //fetch next billing amount calculation
            if ($subscriptionChargebeeId && !empty($subscriptionStatus) && in_array(
                $subscriptionStatus,
                ChargeBeeSubscriptionStatusEnum::renewableStatus()
            )) {
                if ($renewalEstimateData = $this->getSubscriptionRenewalEstimate($subscriptionChargebeeId)) {
                    if ($nextBillingTotalCents = data_get($renewalEstimateData, 'invoice_estimate.total')) {
                        $subscription[ChargebeeSubscription::NEXT_BILLING_AMOUNT_KEY] = $nextBillingTotalCents;
                    }
                }
            }

            /** @var ChargebeeSubscription $subscriptionObj */
            $subscriptionObj = ChargebeeSubscription::updateOrCreate(
                ['uuid' => $subscriptionChargebeeId, 'user_id' => $user->id],
                ['data' => $this->prepareRawSubscriptionData($subscription)]
            );

            //update assigned_user_id parameter if subscription was created
            if ($subscriptionObj->wasRecentlyCreated) {
                $subscriptionObj->assigned_user_id = $user->id;
            }

            //update payment method if presented
            if ($paymentMethod) {
                $subscriptionObj->payment_method = $paymentMethod;
            }

            //save to db if changes was provided
            if ($subscriptionObj->isDirty()) {
                $subscriptionObj->save();
            }


            $this->checkAndCreateInternalSubscription($user);

            //notify admins about user with two active subscriptions
            $this->notifyAdminsAboutTwoActiveSubscriptionsIfRequired($user);
        } else {
            ob_start();
            debug_print_backtrace();
            $log = ob_get_clean();
            \Log::error(
                'No ability to fetch matched to chargebee customer user record, customer email:' . $customerEmail . ' customer ID: ' . $customerChargebeeId . ' subscription ID:' . $subscriptionChargebeeId . PHP_EOL . ' Backtrace:' . PHP_EOL . $log,
                compact('customerEmail', 'customerChargebeeId'),
            );
            // TODO: send admin email notification
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPlans()
    {
        $this->configureEnvironment();
        $plans = [];

        $all = ChargeBee_Plan::all(array('limit' => 100));
        foreach ($all as $entry) {
            $plan    = $entry->plan();
            $plans[] = $plan->getValues();
        }

        return $plans;
    }

    /**
     * @throws ChargebeeConfigurationFailure
     */
    public function getSubscriptionByUUID($uuid)
    {
        $this->configureEnvironment();
        return ChargeBee_Subscription::retrieve($uuid);
    }

    /**
     * @throws ChargebeeEventFailed
     */
    public function handleWebhook($eventData): bool
    {
        $eventType = $eventData['event_type'] ?? null;
        if ($handler = $this->getWebhookHandler($eventType)) {
            (new $handler($eventData))->handle();
            return true;
        }
        throw new ChargebeeEventFailed('No matched handlers provided');
    }

    private function getWebhookHandler($event)
    {
        $this->webhooksHandlersMap = config('chargebee.handlers');
        return $this->webhooksHandlersMap[$event]['handler'] ?? null;
    }

    /**
     * @throws Throwable
     */
    public function withdrawFoodpoints($invoiceId): void
    {
        DB::transaction(
            static function () use ($invoiceId) {
                $invoiceDb = ChargebeeInvoiceModel::where('invoice_id', $invoiceId)
                    ->where('status', ChargebeeInvoiceModel::STATUS_PAID)
                    ->get()
                    ->keyBy('invoice_id');

                foreach ($invoiceDb as $record) {
                    $invoice = $record->data;

                    $amount = 0;
                    if (isset($invoice['line_items'])) {
                        foreach ($invoice['line_items'] as $line_item) {
                            if (isset($line_item['entity_id'])) {
                                $entityId = ChargebeeService::removeCurrencyFromChargebeePlanId($line_item['entity_id']);
                                if (isset($foodpointsConfig[$entityId])) {
                                    $amount = (int)$foodpointsConfig[$entityId];
                                }
                            }
                        }
                    }

                    // TODO:: refactor as standalone method
                    if ($amount > 0) {
                        $user = User::find($record->assigned_user_id);
                        # set user balance
                        try {
                            $user->withdraw(
                                $amount,
                                [
                                    'description' => "Withdraw of $amount FoodPoints due to payment refund with chargebee invoice: $invoiceId date:" . Carbon::createFromTimestamp(
                                        $record->invoice_date
                                    )->format('Y-m-d H:i:s')
                                ]
                            );
                        } catch (ExceptionInterface $e) {
                            logError($e);
                            return response()->json(['success' => false]);
                        }
                    }

                    $record->processed = ChargebeeInvoiceModel::PROCESSED;
                    $record->status    = ChargebeeInvoiceModel::STATUS_REFUNDED;
                    $record->save();
                }

            },
            5
        );
    }

    /**
     * Return AboChallenge ID based on chargebee plan ID (string)
     * TODO: improper return type
     * @param string $planId
     * @param string $lang
     * @return int
     */
    public static function getChallengeIdByChargebeePlanId($planId = null, $lang = 'general')
    {
        $challengeId = CourseId::getFirstTimeChallengeId($lang);
        if ($selectedChallengeId = self::issetChallengeIdByChargebeePlanId($planId, $lang)) {
            $challengeId = $selectedChallengeId;
        }

        return $challengeId;
    }
}

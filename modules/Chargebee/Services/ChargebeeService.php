<?php

namespace Modules\Chargebee\Services;

use App\Enums\Admin\Permission\RoleEnum;
use App\Enums\DatabaseTableEnum;
use App\Enums\User\UserStatusEnum;
use App\Events\UserQuestionnaireChanged;
use App\Helpers\CacheKeys;
use App\Helpers\Calculation;
use App\Jobs\AutomationUserCreation;
use App\Jobs\RecalculateRecipes;
use App\Mail\MailMailable;
use App\Models\User;
use App\Services\Users\UserService;
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
use Modules\Chargebee\Enums\ChargebeeSubscriptionStatus;
use Modules\Chargebee\Enums\CurrenciesEnum;
use Modules\Chargebee\Exceptions\ChargebeeConfigurationFailure;
use Modules\Chargebee\Exceptions\ChargebeeEventFailed;
use Modules\Chargebee\Models\ChargebeeInvoice as ChargebeeInvoiceModel;
use Modules\Chargebee\Models\ChargebeeSubscription;
use Modules\Chargebee\Notifications\UserHasTwoActiveChargebeeSubscriptionsNotification;
use Modules\Course\Enums\CourseId;
use Modules\Course\Models\Course;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;
use Modules\Internal\Models\AdminStorage;
use Password;
use Throwable;

/**
 * TODO: refactor to simplify. Another god class is bad for mental health...
 * TODO: class has 1053 lines of code.  Avoid really long classes.
 * TODO: class has 11 public methods. Consider refactoring ChargebeeService to keep number of public methods under 10.
 * TODO: class has a coupling between objects value of 33. Consider to reduce the number of dependencies under 13.
 * TODO: class has an overall complexity of 165 which is very high.
 * TODO: Typehint missing
 */
class ChargebeeService
{
    private array $webhooksHandlersMap; // todo: totally unnecessary property

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
    public function handleSubscriptionCreated($eventData, $checkExistingTheSameUUIDPlanId = true)
    {

        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
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

        $_user = User::ofEmail($customerEmail)->orderBy('status', 'DESC')->first();

        // reactivation user checking place
        $reactivationConditions = false;
        if (!empty($_user)){
            $reactivationConditions = $this->userMetReactivationConditions($order, $_user);
        }


        // check if user already has subscription with the same UUID and plan_id
        $chargebeeSubscriptionWithSameUUIDAndPlanID = false;
        if ($_user && $checkExistingTheSameUUIDPlanId) {
            $subscriptions = $_user->assignedChargebeeSubscriptions()->get();
            foreach ($subscriptions as $subscription) {
                $planIdOld = self::getChargebeePlanIdFromSubscriptionData($subscription->data);
                if ($subscription->uuid == $order['id'] && $planIdOld == $order['plan_id']) {
                    $chargebeeSubscriptionWithSameUUIDAndPlanID = true;
                    break;
                }
            }
        }

        /// debug part
//        $subscriptions = $_user->assignedChargebeeSubscriptions()->get()->toArray();
//        $conditionsStr = json_encode($reactivationConditions,JSON_PRETTY_PRINT);
//        $conditionsStr = nl2br(stripcslashes($conditionsStr));
//        $msg = 'DEBUG:: User reactivation allowed, #' . $subscriptionId . ' plan_id=' . $order['plan_id'] . ' USER_ID=' . $_user->id.' Conditions:'.$conditionsStr;
//        $msg .= var_export($subscriptions,true);
//        Log::info($msg);
        /// debug part



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
                // if user not exists but challenge exists by chargebee plan_id, it's case when not all plan_ids are in config
                (
                    empty($_user)
                    &&
                    self::issetChallengeIdByChargebeePlanId($trimmedChargebeePlanId) !== false
                )
                ||
                // if user not exists but challenge exists by chargebee plan_id and user's lang
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
            $courseId = self::getChallengeIdByChargebeePlanId($trimmedChargebeePlanId, $_user->lang);


            // probably could be place of issue with double subscriptions
            // updating data from chargebee for the current user, whenever user not exists before, we need to sync his transactions
            $this->refreshSubscriptionData($_user); // todo: unhandled exception

            // TODO:: review challenges @NickMost???
            if (!$existUser) {

                // Totally new user
                $_user->setFirstTimeCourse();
                $_user->addCourseIfNotExists($courseId);

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

//                $_user->status = true;
//                $_user->save();
            }


            // user exists and subscription hasn't been processed yet
            if ($existUser && !$chargebeeSubscriptionWithSameUUIDAndPlanID) {
                // user hasn't subscription with the same UUID, it's reactivation or activation for user who hasn't before subscription
                // reactivation notification

                $conditionsStr = json_encode($reactivationConditions,JSON_PRETTY_PRINT);
                $conditionsStr = nl2br(stripcslashes($conditionsStr));
                $conditionsStr = str_replace(['    '],['&ensp;&ensp;&ensp;'],$conditionsStr);

                $msg = 'User reactivation allowed, #' . $subscriptionId . ' plan_id=' . $order['plan_id'] . ' USER_ID=' . $_user->id.' Conditions:'.$conditionsStr;
                Log::info($msg);

                if (is_array($reactivationConditions) && $reactivationConditions['can_be_reactivated']){

                    $this->reactivateUser($_user,$conditionsStr, $currentDateTime);

                    $msg = 'User reactivation done , #' . $subscriptionId . ' plan_id=' . $order['plan_id'] . ' USER_ID=' . $_user->id.' Conditions:'.$conditionsStr;
                    Log::info($msg);
                }
                // if not exists active subscription but subscriptions presents
                elseif(
                    is_array($reactivationConditions)
                    &&
                    !empty($reactivationConditions['required']['chargebee_no_active_no_nonrenewing_subscriptions'])
                    &&
                    !empty($reactivationConditions['required']['chargebee_subscription_presents'])
                )
                    {

                    $msg = 'User reactivation NOT allowed , #' . $subscriptionId . ' plan_id=' . $order['plan_id'] . ' USER_ID=' . $_user->id.' Conditions:'.$conditionsStr;
                    Log::info($msg);

                    $notificationsEmails    = config('chargebee.adminNotificationEmails');
                    $mailObject = new MailMailable('emails.userReactivationAdminNotAllowed', [
                        'mailBodySubject'=>__('email.userReactivationAdminNotAllowed.mail_subject'),
                        'user'=>$_user,
                        'orderId'=>$order['order_id'],
                        'planId'=>$order['plan_id'],
                        'reactivationDate'=>$currentDateTime,
                        'reactivationConditions'=>$conditionsStr
                    ]);
                    $mailObject->from(config('mail.from.address'), config('mail.from.name'))
                        ->to($notificationsEmails)
                        ->subject(__('email.userReactivationAdminNotAllowed.mail_subject'))
                        ->onQueue('emails');
                    Mail::queue($mailObject);
                }
                // other cases when user exists, but issues with subsction
                else{
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

            }


            // sync for users who haven't reactivation
            $_user->setFirstTimeCourse();
            $_user->addCourseIfNotExists($courseId);

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

        // update subscription status and processed time

        if (!empty($subscriptionId)){
            ChargebeeSubscription::where('uuid', $subscriptionId)->update(['processed' => Carbon::now(),'status'=>$order['eventData']['content']['subscription']['status']]);
        }
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
     *
     *
     * //--== END HOOK HANDLERS ==--
     *
     * /**
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

//        $this->checkAndCreateInternalSubscription($user);
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
                \DB::table(DatabaseTableEnum::CHARGEBEE_SUBSCRIPTIONS)
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

    /** uses for reactivation flow, sync only exists in the system */
    public function updateUserExistsSubscriptions(User $user, $subscriptions = null)
    {
        if (!is_null($subscriptions)) {
            $subscriptions = $this->prepareSubscriptionDataFromChargebee($subscriptions);
            foreach ($subscriptions as $subscriptionData) {
                if ($subscriptionObject = ChargebeeSubscription::query()
                    ->where('uuid','=',$subscriptionData['id'])
                    ->where('user_id','=', $user->id)->first()
                ) {
                    $subscriptionObject->data = $subscriptionData;
                    //set or update payment_method if presented
                    if (!empty($subscriptionData['payment_method'])) {
                        $subscriptionObject->payment_method = data_get($subscriptionData, 'payment_method');
                    }
                    $subscriptionObject->save();
                }
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
                ChargebeeSubscriptionStatus::renewableStatus()
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

    // TODO:: check and replace where it's required, probably subscription create, changed, resumed, reactivated etc.
    protected function checkAndCreateInternalSubscription($user)
    {
        if (empty($user->subscription)) {
            // TODO:: check if subscription exists but not exists in user's scope - then create
            $chargebeePlanId = $user->getLastChargebeePlanId();
            if (empty($chargebeePlanId)) {
                return;
            }
            $courseId = self::getChallengeIdByChargebeePlanId($chargebeePlanId, $user->lang);
            $user->addCourseIfNotExists($courseId);

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
                                ChargebeeSubscriptionStatus::potentiallyActiveStatus()
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

                // TODO:: refactor....
//                $hasActiveSubscription = !empty($subscriptions);
//                foreach ($subscriptions as $subscription) {
//                    if (!empty($subscription['cancelled_at']) && $subscription['cancelled_at'] < $now) {
//                        $hasActiveSubscription = false;
//                    }
//                }

                // similar code ChargebeeService::1555
                if (!empty($subscriptions)){
                    foreach ($subscriptions as $subscription) {
                        if (
                            empty($subscription['cancelled_at'])
                            ||
                            (
                                !empty($subscription['cancelled_at']) && $subscription['cancelled_at'] >= $now
                            )
                        ) {
                            $hasActiveSubscription = true;
                        }
                    }
                }
                // user hasn't active subscription, need to cancel
            }
            // TODO:: cover subscription status and chargebe item status in database

            if (!$hasActiveSubscription) {
                $user->subscriptions()->where('active', true)->update(['ends_at' => $subscriptionCancelledAt]);
            }

            $this->syncSubscriptionsData($eventData, $user);
        }
    }

    /** TODO:: refactor */
    public function getUserBySubscriptionData($eventData){
        $customerEmail = data_get($eventData, 'content.customer.email');
        if (empty($customerEmail)) {
            $customerEmail = data_get($eventData, 'content.billing_address.email');
        }
        if (empty($customerEmail)) {
            $customerEmail = data_get($eventData, 'content.subscription.customer_id');
        }

        $user = !empty($customerEmail) ? User::ofEmail($customerEmail)->first() : null;
        return $user;
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

//            $user = !empty($customerEmail) ? User::ofEmail($customerEmail)->first() : null;

            $user = $this->getUserBySubscriptionData($eventData);
        }

        if (!empty($user)) {
            /** @var User $user */
            $user->update(['chargebee_id' => $customerChargebeeId]);

            //fetch next billing amount calculation
            if ($subscriptionChargebeeId && !empty($subscriptionStatus) && in_array(
                $subscriptionStatus,
                ChargebeeSubscriptionStatus::renewableStatus()
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


//            $this->checkAndCreateInternalSubscription($user);

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
        $courseId = CourseId::getFirstTimeChallengeId($lang);
        if ($selectedChallengeId = self::issetChallengeIdByChargebeePlanId($planId, $lang)) {
            $courseId = $selectedChallengeId;
        }

        return $courseId;
    }

    // based on https://foodpunk.atlassian.net/wiki/spaces/APP/pages/2613116930/2024-10-11+Automatic+reactivation+of+user+account
    public function userMetReactivationConditions($order, User $user = null){

        $CONST_DATE = '2019-06-30';
        $CONST_MINIMUM_RECIPES = 90;
        $CONST_MINIMUM_BALANCE = 100;

        $metConditions = [
            'general'=>[
                'user_exists'=>false,
            ],
            'required'=>[
                'chargebee_subscription_presents'=>false,
                'chargebee_no_active_no_nonrenewing_subscriptions'=>false,
                'chargebee_subscription_must_have_status_canceled'=>false,
                'status_at_least_one_subscription_presents'=>false,
                'status_must_be_finished'=>false,
                'status_no_subscription_has_status_active'=>false,
                'questionnaire_exists'=>false,
                'recipes_minimum_amount_90'=>false,
            ],
            'one_of'=>[
                'membership_starts_before_date_20190630'=>false,
                'registration_before_date_20190630'=>false,
                'recent_questionnaire_created_no_more_2_years_ago'=>false,
                'has_foodpoints_minimum_100'=>false,
            ],
        ];

        if (empty($user)) return $metConditions;

        $metConditions['general']['user_exists'] = true;

        $triggerDate = Carbon::parse($CONST_DATE);

        if ($user->created_at<=$triggerDate){
            // condition: Additional: The registration date is dated before 2019-06-30.
            $metConditions['one_of']['registration_before_date_20190630'] = true;
        }

        if ($user->allRecipes()->count()>=$CONST_MINIMUM_RECIPES){
            // condition: Recipes: min. 90 recipes are present
            $metConditions['required']['recipes_minimum_amount_90'] = true;
        }

        //condition: Additional: The customer has previously purchased foodpoints. In the transaction history a transaction is noted as “Deposit of X FoodPoints Admin” or “Deposit of X FoodPoints based on chargebee invoice” AND Foodpoints balance is > 100 FP
        if ($user->balance>$CONST_MINIMUM_BALANCE){
            $transactions = $user->walletTransactions()->get();
            if ($transactions){
                foreach($transactions as $transaction){
                    if (
                        !empty($transaction->meta['description'])
                        &&
                        (
                            strpos($transaction->meta['description'],'FoodPoints Admin')!==false
                            ||
                            strpos($transaction->meta['description'],'FoodPoints based on chargebee invoice')!==false
                        )
                    ){
                        $metConditions['one_of']['has_foodpoints_minimum_100'] = true;
                        break;
                    }
                }
            }
        }



        $latestQuestionnaire = $user->latestQuestionnaire()->first();
        if (!empty($latestQuestionnaire)){
            // condition: Questionnaire:exists
            $metConditions['required']['questionnaire_exists'] = true;
            if($latestQuestionnaire->created_at > Carbon::now()->subYears(2)){
                // condition: Additional: The most recent questionnaire was created no more than two years ago
                $metConditions['one_of']['recent_questionnaire_created_no_more_2_years_ago'] = true;
            }
        }

        $userSubscriptions = $user->subscriptions()->get();
        if (!empty($userSubscriptions)){
            // condition: Status:At least one subscription is present
            $metConditions['required']['status_at_least_one_subscription_presents'] = true;


            // condition: Status:Status must be “finished”
            $metConditions['required']['status_no_subscription_has_status_active'] = true;
            foreach($userSubscriptions as $subscription){

                if ($subscription->created_at<=$triggerDate){
                    // condition: Additional: The previous membership started before 30.06.2019
                    $metConditions['one_of']['membership_starts_before_date_20190630'] = true;
                }

                if ($subscription->active == 1 && empty($subscription->ends_at)){
                    $metConditions['required']['status_no_subscription_has_status_active'] = false;
                }

            }
        }

        // condition: Status:Status must be “finished”
        $userLatestSubscription = $user->getLatestSubscription();
        if (empty($userLatestSubscription) || (!empty($userLatestSubscription) && $userLatestSubscription->ends_at<Carbon::now())){
            $metConditions['required']['status_must_be_finished'] = true;
        }


        $this->configureEnvironment();
        $chargebeeIds = $this->getChargebeeCustomerIds($user);
        if (!empty($chargebeeIds) && is_array($chargebeeIds)) {
            try {
                $subscriptions = ChargeBee_Subscription::all(
                    array(
                        "sortBy[asc]" => "updated_at",
                        "customerId[in]" => $chargebeeIds,
                        'limit' => 100,
                    )
                );
                if (!is_null($subscriptions)) {
                    $this->updateUserExistsSubscriptions($user, $subscriptions);
                }

            } catch (Throwable $e) {
                logError($e);
            }
        }


        $existChargebeeSubscriptions = \DB::table(DatabaseTableEnum::CHARGEBEE_SUBSCRIPTIONS)
            ->where('assigned_user_id', '=',$user->id)
            ->where('uuid','!=',$order['id'])
            ->get();


        if (!empty($existChargebeeSubscriptions)){
            // condition: Chargebee subscription: is present
            $metConditions['required']['chargebee_subscription_presents'] = true;

            $restrictedStatuses = [ChargebeeSubscriptionStatus::ACTIVE->value,ChargebeeSubscriptionStatus::NON_RENEWING->value];

            // condition: Chargebee subscription:no active or non renewing subscription(s)
            $metConditions['required']['chargebee_no_active_no_nonrenewing_subscriptions'] = true;
            foreach ($existChargebeeSubscriptions as $existSubscription) {
                $data = json_decode($existSubscription->data, true);
                if (in_array($data['status'],$restrictedStatuses)){
                    $metConditions['required']['chargebee_no_active_no_nonrenewing_subscriptions'] = false;
                }

                // condition: Chargebee subscription:Chargebee subscription must have status “cancelled”
                if ($data['status']==ChargebeeSubscriptionStatus::CANCELLED->value){
                    $metConditions['required']['chargebee_subscription_must_have_status_canceled'] = true;
                }
            }
        }

        $metConditions['result']['general'] = array_reduce($metConditions['general'], function($result, $value):bool {
            return (bool)$result*$value;

        },true);

        $metConditions['result']['required'] = array_reduce($metConditions['required'], function($result, $value):bool {
            return (bool)$result*$value;

        },true);

        $metConditions['result']['one_of'] = array_reduce($metConditions['one_of'], function($result, $value):bool {
            return (bool)$result||$value;

        },false);

        $canBeReactivated = array_reduce($metConditions['result'], function($result, $value):bool {
            return (bool)$result*$value;

        },true);

        unset($metConditions['general']);
        unset($metConditions['result']);

        $metConditions['can_be_reactivated'] = $canBeReactivated;


        return $metConditions;

    }

    public function reactivateUser(User $user, $conditionsStr = '', $reactivationDate){


        $CONST_FOODPOINTS_MINIMUM = 150;

        //Status: Create a new active subscription in the account under “Status”.
        $user->status = true;
        $user->save();
        $user->createSubscription();
        //Questionnaire: Force visibility of questionnaire for user is being activated
        $user->latestQuestionnaire()->update(['is_editable' => 1]);
        UserQuestionnaireChanged::dispatch($user->id);

        //Balance: the balance should be at least 150 foodpoints. If the balance is less than 150 foodpoints, add the difference. If the account already has more than 150 foodpoints, no additional foodpoints must be added.
        if ($user->balance<$CONST_FOODPOINTS_MINIMUM){
            $amount = $CONST_FOODPOINTS_MINIMUM - $user->balance;
            $user->deposit($amount, ['description' => "Deposit of $amount FoodPoints (Reactivation)"]);
        }



        //Weeky plan: Generate weekly plan.


//        $recipeIds = $user->allRecipes()->orderBy('recipes.id')->pluck('recipes.id')->toArray();
//        RecalculateRecipes::dispatchSync($user, $recipeIds);
        Calculation::_generate2subscription($user);



        //
        //If user doesn’t own course ID 2 or 25, add ID 2 for german users, ID 25 for english speaking users. Start date: Day of reactivation.
        //
        //for chargebee plans with a course other than ID 2 or 25 the specific course should be added with start date as day of reactivation.
        //
        // If minimum start date for this course exists and it is after the current date, start course at minimum start date.

        $addedCourses = [];

        $user->setFirstTimeCourse();

        $planId = '-';
        $courseId = null;
        $latestChargebeeSubscription = ChargebeeSubscription::where('assigned_user_id',$user->id)->orderBy('created_at','desc')->first();
        if (!empty($latestChargebeeSubscription)){
            $planId = $latestChargebeeSubscription->SubscriptionName;
            if (!empty($planId)){
                $trimmedChargebeePlanId = self::prepareChargebeePlanId($planId);
                $courseId = self::getChallengeIdByChargebeePlanId($trimmedChargebeePlanId, $user->lang);
                if (!empty($courseId)){

                    if (!$user->courseExists($courseId)){
                        $user->addCourseIfNotExists($courseId);
                        $addedCourses[] = $courseId;
                    }
                }
            }
        }

        if (empty($courseId)){
            if ($user->lang=='en' && (!$user->courseExists(CourseId::EN_30_DAYS->value))){
                $user->addCourseIfNotExists(CourseId::EN_30_DAYS->value);
                $addedCourses[] = CourseId::EN_30_DAYS->value;
            }
            elseif (!$user->courseExists(CourseId::DE_30_DAYS->value)){
                $user->addCourseIfNotExists(CourseId::DE_30_DAYS->value);
                $addedCourses[] = CourseId::DE_30_DAYS->value;
            }
        }


        // notification to Admin

        if (!empty($addedCourses)){
            sort($addedCourses);
            $tmp = [];
            foreach($addedCourses as $courseId){
                $tmp[] = Course::find($courseId)->title.'('.$courseId.')';
            }
            $addedCoursesStr = implode(', ',$tmp);
        }
        else{
            $addedCoursesStr = ' - ';
        }


        // TODO:: to think about courses added before
        $notificationsEmails    = config('chargebee.adminNotificationEmails');
        $mailObject = new MailMailable('emails.userReactivationAdminSuccess', [
            'mailBodySubject'=>__('email.userReactivationAdminSuccess.mail_subject'),
            'user'=>$user,
            'reactivationDate'=>$reactivationDate,
            'planId'=>$planId,
            'addedCoursesStr'=>$addedCoursesStr,
            'reactivationConditions'=>$conditionsStr,
        ]);

        $mailObject->from(config('mail.from.address'), config('mail.from.name'))
        ->to($notificationsEmails)
        ->subject(__('email.userReactivationAdminSuccess.mail_subject'))
        ->onQueue('emails');

        Mail::queue($mailObject);


        app()->setLocale($user->lang);
        // notification to User
        $mailObject = new MailMailable('emails.userReactivationUser', [
            'mailBodySubject'=>__('email.userReactivationUser.mail_subject'),
            'user'=>$user,
        ]);
        $mailObject->from(config('mail.from.address'), config('mail.from.name'))
            ->to($user->email)
            ->subject(__('email.userReactivationUser.mail_subject'))
            ->onQueue('emails');

        Mail::queue($mailObject);

    }

    public function userHasActiveChargebeeSubscription(User $user){

        $hasActiveSubscription            = false;
        $subscriptions                    = $user->assignedChargebeeSubscriptions;

        if (!$subscriptions->isEmpty()) {
            $now = Carbon::now();
            $subscriptions = $subscriptions
                ->filter(
                    fn($item) => (
                        in_array(
                            $item->data['status'],
                            ChargebeeSubscriptionStatus::potentiallyActiveStatus()
                        )
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
                ->sortBy('started_at');

            if (!empty($subscriptions)){
                foreach ($subscriptions as $subscription) {
                    if (
                        empty($subscription->cancelled_at)
                        ||
                        (
                            !empty($subscription->cancelled_at) && $subscription->cancelled_at >= $now
                        )
                    ) {
                        $hasActiveSubscription = $subscription;
                    }
                }
            }
            // user hasn't active subscription, need to cancel
        }
        return $hasActiveSubscription;
    }

    // TODO:: refactor ASAP, it's trick for non-creating meal-plans
    public function subscriptionChangedEvent(User $user){

        $this->refreshSubscriptionData($user);
        if($chargebeeSubscription = $this->userHasActiveChargebeeSubscription($user)){

            $userCreationPlans      = config('chargebee.create_user_plan');
            $chargebeePlanId = ChargebeeService::getChargebeePlanIdFromSubscriptionData($chargebeeSubscription->data);


            //$chargebeeSubscription
            $trimmedChargebeePlanId = self::prepareChargebeePlanId($chargebeePlanId);

            if (
                !empty($chargebeePlanId)
                &&
                (
                        in_array($trimmedChargebeePlanId, $userCreationPlans)
                        ||
                        self::issetChallengeIdByChargebeePlanId($trimmedChargebeePlanId, $user->lang) !== false
                )
            ){
                $courseId = self::getChallengeIdByChargebeePlanId($trimmedChargebeePlanId, $user->lang);

                // user has subscription, need to check all conditions
                $user->maybeCreateSubscription();
                $user->status = true;
                $user->save();

                $user->setFirstTimeCourse();
                $user->addCourseIfNotExists($courseId);

                $latestQuestionnaire = $user->latestQuestionnaire()->first();
                // if exists questionnaire, otherwise welcome bonus add while creation first questionnaire
                if (!empty($latestQuestionnaire)){
                    // checking balance
                    if ($user->balance==0){
                        app(UserService::class)->addUserWelcomeBonus($user);
                    }

                    if (!empty($user->dietdata) && $user->questionnaireApproved === true) {


                        $totalUsersRecipesAmount = $user->allRecipes()->count();
                        if (empty($totalUsersRecipesAmount)){


                            SyncUserExcludedIngredientsJob::dispatch($user);


                            #Running job for distribute random recipes and generate recipes/
                            $adminStorageData = AdminStorage::where('key', "meal_plan_generation_$user->id")->first();
//                            $canRunJob        = is_null($adminStorageData) || $adminStorageData->data === 'on';
                            AutomationUserCreation::dispatchSync($user);
                            // Clean up storage if model exists
                            if (!is_null($adminStorageData)) {
                                AdminStorage::where('key', "meal_plan_generation_$user->id")->delete();
                            }

                            $totalUsersRecipesAmount = $user->allRecipes()->count();
                            $recipesInMealPlanForFutureAmount = $user->recipes()->where('meal_date','>',Carbon::now())->count();
                            if (!empty($totalUsersRecipesAmount) && empty($recipesInMealPlanForFutureAmount)){
                                Calculation::_generate2subscription($user);
                            }

                        }


                    }

                }

            }

        }

    }
}

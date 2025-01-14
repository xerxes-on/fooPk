<?php

namespace App\Http\Traits\Questionnaire;

use App\Exceptions\Questionnaire\QuestionnaireNotApproved;
use App\Helpers\CacheKeys;
use App\Jobs\AutomationUserCreation;
use App\Models\User;
use Modules\Chargebee\Services\ChargebeeService;
use App\Services\Users\UserNutrientsService;
use App\Services\Users\UserService;
use Illuminate\Support\Facades\Cache;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;
use Modules\Internal\Models\AdminStorage;

trait CanHandleQuestionnaireCreation
{
    /**
     * Handle questionnaire creation event. Used to process first time questionnaire creation event.
     * @throws \Exception
     */
    public function processCreateEvent(User $user): void
    {
        // TODO:: refactor to user's service method | TRIGGERS EXCEPTIONS
        app(ChargebeeService::class)->configureEnvironment();
        app(ChargebeeService::class)->refreshUserSubscriptionData($user);

        # create Subscription if not active found
        // TODO::refactor that @NickMost
        // TODO:: check if exists chargebee subscriotioin......... then create it....
        //		if (empty($user->subscription)) {
        //          $chargebeePlanId = $user->getLastChargebeePlanId();
        //          $challengeId     = AboChallenges::getChallengeIdByChargebeePlanId($chargebeePlanId, $user->lang);
        //			$user->createSubscription($challengeId);
        //		}

        Cache::forget(CacheKeys::userQuestionnaireExists($user->id));
        $user->refresh();

        if ($user->balance==0){
            app(UserService::class)->addUserWelcomeBonus($user);
        }

        $user->calc_auto = true;
        $user->save();


        // TODO:: refactor to user's service method
        if($chargebeeSubscription = app(ChargebeeService::class)->userHasActiveChargebeeSubscription($user)){

            $userCreationPlans      = config('chargebee.create_user_plan');
            $chargebeePlanId = ChargebeeService::getChargebeePlanIdFromSubscriptionData($chargebeeSubscription->data);

            //$chargebeeSubscription
            $trimmedChargebeePlanId = app(ChargebeeService::class)::prepareChargebeePlanId($chargebeePlanId);

            if (
                !empty($chargebeePlanId)
                &&
                (
                    in_array($trimmedChargebeePlanId, $userCreationPlans)
                    ||
                    app(ChargebeeService::class)::issetChallengeIdByChargebeePlanId($trimmedChargebeePlanId, $user->lang) !== false
                )
            ) {
                $user->maybeCreateSubscription();
            }
        }

        // check subscription....


        // Early exit if user has no subscription
        // TODO:: review @NickMost, if it's related to challenges PRIO - 1 ???
        if (empty($user->subscription)) {
            return;
        }

        try {
            $this->processApprovedQuestionnaire($user);
        } catch (QuestionnaireNotApproved) {
            // questionnaire has been not approved, need manager's review
            $adminEditUserUrl = route('admin.model.edit', ['adminModel' => 'users', 'adminModelId' => $user->id]);
            $adminEditUserUrl = str_replace('meinplan', 'static', $adminEditUserUrl);
            send_raw_admin_email(
                "User $user->email (#$user->id) has marked special needs!  <a href='" . $adminEditUserUrl . "' target='_blank'>User edit link</a>",
                'Nutritionist needs to check'
            );
        }
    }

    /**
     * @throws QuestionnaireNotApproved
     */
    private function processApprovedQuestionnaire(User $user): void
    {
        if (!$user->isQuestionnaireExist() || $user->questionnaire_approved === false) {
            throw new QuestionnaireNotApproved();
        }
        // fist time, questionnaire hasn't exception
        // important to save because it's first time
        app(UserNutrientsService::class)->checkAndUpdateDietData($user);
        $user->refresh();
        SyncUserExcludedIngredientsJob::dispatch($user);

        #Running job for distribute random recipes and generate recipes/
        $adminStorageData = AdminStorage::where('key', "meal_plan_generation_$user->id")->first();
        $canRunJob        = is_null($adminStorageData) || $adminStorageData->data === 'on';
        AutomationUserCreation::dispatchIf($canRunJob, $user)
            ->onQueue('high')
            ->delay(now()->addMinutes(5));
        // Clean up storage if model exists
        if (!is_null($adminStorageData)) {
            AdminStorage::where('key', "meal_plan_generation_$user->id")->delete();
        }
    }
}

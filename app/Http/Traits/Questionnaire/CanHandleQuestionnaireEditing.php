<?php

declare(strict_types=1);

namespace App\Http\Traits\Questionnaire;

use App\Jobs\ActionsAfterChangingFormular;
use App\Models\User;
use App\Models\UserRecipeCalculatedPreliminary;
use App\Services\Users\UserNutrientsService;
use Carbon\Carbon;
use Modules\Chargebee\Services\ChargebeeService;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;
use Modules\Internal\Models\AdminStorage;

trait CanHandleQuestionnaireEditing
{
    /**
     * Handle questionnaire editing event.
     * TODO: Cyclomatic Complexity of 11. proper refactor required
     * @throws \Exception
     */
    public function processEditingEvent(User $user, bool $notifyAdmin = true): void
    {
        // if questionnaire not exists start create event
        // TODO:: @NickMost refactor that when flow will be ok from mobile app
        $isQuestionnaireExist = $user->isQuestionnaireExist();
        if (
            !$isQuestionnaireExist
            ||
            (
                $isQuestionnaireExist
                &&
                $user->latestBaseQuestionnaire()->count() === 1
                &&
                $user->latestBaseQuestionnaire()->first()->created_at->gte(Carbon::now()->subMinute())
            )
        ) {
            $this->processCreateEvent($user);
            return;
        }
        // TODO:: @NickMost refactor that when flow will be ok from mobile app

        // TODO:: refactor to user's service method | TRIGGERS EXCEPTIONS
        app(ChargebeeService::class)->configureEnvironment();
        app(ChargebeeService::class)->refreshUserSubscriptionData($user);

        $chargebeePlanId = $user->getLastChargebeePlanId();
        $challengeId     = ChargebeeService::getChallengeIdByChargebeePlanId($chargebeePlanId, $user->lang);

        if (!empty($challengeId)) {
            $user->addCourseIfNotExists($challengeId);
        }

        $isApproved = $isQuestionnaireExist && $user->questionnaire_approved === true;
        if ($notifyAdmin && !$isApproved) {
            $this->notifyAdmins($user);
            return;
        }

        // Early exit if user has no subscription
        // TODO:: review @NickMost, if it's related to challenges PRIO - 1 ???
        if (empty($user->subscription)) {
            return;
        }

        app(UserNutrientsService::class)->checkAndUpdateDietData($user);

        $user->refresh();

        // TODO:: check is changed meal times

        # user recipe Calculated Preliminary nilled
        if ($user->preliminaryCalc()->count() > 0) {
            UserRecipeCalculatedPreliminary::where('user_id', $user->id)
                ->update(['valid' => null, 'counted' => 0]);
        }

        $jobStartHash = AdminStorage::generateAfterFormularChangeJobHash($user->getKey());
        SyncUserExcludedIngredientsJob::dispatch($user);
        ActionsAfterChangingFormular::dispatch($user, $jobStartHash)->onQueue('high')->delay(now()->addSeconds(5));

        // calc_auto review....
        // if second time check - challenge $user->subscription
    }

    private function notifyAdmins(User $user): void
    {
        $adminEditUserUrl = route('admin.model.edit', ['adminModel' => 'users', 'adminModelId' => $user->id]);
        $adminEditUserUrl = str_replace('meinplan', 'static', $adminEditUserUrl);

        switch ($user->calc_auto) {
            case true:
                $emailText = sprintf(
                    'User %s (#%s) has changed questionnaire but can not be approved! <a href="%s" target="_blank">User edit link</a>',
                    $user->email,
                    $user->email,
                    $adminEditUserUrl
                );
                $subject = 'Questionnaire has been changed!';
                break;
            case false:
                $emailText = sprintf(
                    'User %s (#%s) has changed questionnaire and has calc_auto is disabled! <a href="%s" target="_blank">User edit link</a>',
                    $user->email,
                    $user->email,
                    $adminEditUserUrl
                );
                $subject = 'Questionnaire has been changed and calc_auto false!';
        }

        send_raw_admin_email($emailText, $subject);
    }
}

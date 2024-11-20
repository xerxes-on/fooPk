<?php

namespace App\Jobs;

use App\Helpers\Calculation;
use App\Http\Traits\CanGetProperty;
use App\Http\Traits\Queue\HandleLastStartedJob;
use App\Mail\MailMailable;
use App\Models\{User, UserRecipeCalculated, UserRecipeCalculatedPreliminary};
use App\Services\Mails\MailService;
use App\Services\Users\UserRecipeCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Modules\Internal\Enums\JobProcessingEnum;
use Modules\Internal\Models\AdminStorage;

/**
 * Class ActionsAfterChangingFormular
 *
 * @package App\Jobs
 */
final class ActionsAfterChangingFormular implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HandleLastStartedJob;
    use CanGetProperty;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param int/null  $relatedJobHash
     */
    public function __construct(private User $user, $relatedJobHash = false)
    {
        $this->relatedJobHash = $relatedJobHash;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->initRelatedJobId(JobProcessingEnum::AFTER_QUESTIONNAIRE_CHANGE->value);

        // get exist recipe and calculations. They are grouped to remove duplicates.
        $existRecipeIds = $this
            ->user
            ->allRecipes()
//			->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
//			->where('user_recipe_calculated.user_id', $this->user->getKey())
//			->groupBy('recipes.id')
            ->pluck('recipes.id')
            ->toArray();

        if (count($existRecipeIds) === 0) {
            return;
        }
        # ===========================
        # recalculate Recipes to user
        # ===========================


        $userRecipeCalculationService = app(UserRecipeCalculationService::class);
        $mailService                  = app(MailService::class);

        // critical flag, job can't be interrupted if this is true
        $userHasChangedAmountOfMealsPerDay = false;
        $allowJobInterruption = true;
        if ($this->user->isQuestionnaireExist() && $this->user->hasChangedMealPerDay) {
            $userHasChangedAmountOfMealsPerDay = true;
            $allowJobInterruption = false;
        }


        if ($allowJobInterruption && $this->verifyOrFinishJob(JobProcessingEnum::AFTER_QUESTIONNAIRE_CHANGE->value) === false) {
            return;
        }

        /**
         * Added fix for forever invalid recipes, after formular changes.
         * All recipes will be preliminary recalculated, to be sure that invalid too.
         */

        $userId = $this->user->getKey();
        # user recipe Calculated Preliminary nilled
        UserRecipeCalculatedPreliminary::where('user_id', $userId)
            ->update(['valid' => null, 'counted' => 0]);

        $jobStartHash = AdminStorage::generatePreliminaryJobHash($userId);
        PreliminaryCalculation::dispatchSync($this->user, false, $jobStartHash,$allowJobInterruption);


        if ($allowJobInterruption && $this->verifyOrFinishJob(JobProcessingEnum::AFTER_QUESTIONNAIRE_CHANGE->value) === false) {
            return;
        }
        Calculation::_calcRecipe2user($this->user, $existRecipeIds);

        if ($allowJobInterruption && $this->verifyOrFinishJob(JobProcessingEnum::AFTER_QUESTIONNAIRE_CHANGE->value) === false) {
            return;
        }
        // if has been changed amount of meals per day, need to regenerate meal plan
        if ($userHasChangedAmountOfMealsPerDay) {
            $userRecipeCalculationService->processMealPerDayChanges($userId);
        }

        // TODO:: cleanup shopping list????

        // TODO:: add checking for replacement recipes into user_recipe table


        // TODO:: to think about meal plan generation if user hasn't subscription on creation event

        $data = UserRecipeCalculated::where(
            [
                ['user_id', $this->user->getKey()],
                ['invalid', 0],
            ]
        )
            ->whereNotNull('recipe_id')
            ->groupBy('recipe_id')
            ->pluck('recipe_id')
            ->count();


        if (!$userRecipeCalculationService->checkIfUserRecipesCountIsValid($userId)) {
            $recipeCount = $userRecipeCalculationService->getUserRecipesValidCount($userId);
            $mailService->sendRawAdminEmail($this->user->email, $userId);
            \Log::info("Recalculation is done for user {$this->user->email} now $recipeCount valid recipes are left");
        }

        $this->notifyUser();
        AdminStorage::where('key', JobProcessingEnum::AFTER_QUESTIONNAIRE_CHANGE->value . $userId)->delete();
    }


    /**
     * Send email notification to user.
     *
     * @return void
     */
    private function notifyUser(): void
    {
        $userEmail = $this->user->email;

        $mailObject = new MailMailable('emails.recalculatedDone', ['userName' => $this->user->full_name]);

        // Sending email should be delayed by 30 minutes in order not to confuse user with calculation speed.
        $mailObject->from(config('mail.from.address'), config('mail.from.name'))
            ->to($userEmail)
            ->bcc(config('mail.from.address'))
            ->subject('Wir haben deinen Plan angepasst!')
            ->onQueue('emails')
            ->delay(now()->addMinutes(30));

        \Mail::queue($mailObject);
    }
}

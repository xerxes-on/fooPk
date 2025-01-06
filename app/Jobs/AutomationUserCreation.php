<?php

namespace App\Jobs;

use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Helpers\Calculation;
use App\Http\Traits\CanGetProperty;
use App\Mail\MailMailable;
use App\Models\Allergy;
use App\Models\Diet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;

/**
 * Class ActionsAfterChangingFormular
 *
 * @package App\Jobs
 */
class AutomationUserCreation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use CanGetProperty;

    /**
     * Create a new job instance.
     * Only one param is passed into this instance, other should be invoked manually
     */
    public function __construct(protected User $user)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //if user has recipes already we don't need to process it
        $mealPlanRecipesAmount = $this->user->recipes()->count();
        $allRecipesAmount      = $this->user->allRecipes()->count();
        // could be case that user will be affected by monthly recipe distribution before regular creation...WEB-624
        $numberNewRecipes = intval(config("adding-new-recipes.monthly.numberNewRecipes"));
//        if ($allRecipesAmount > $numberNewRecipes || $mealPlanRecipesAmount > 0 ) {
        if ($allRecipesAmount>0 || $mealPlanRecipesAmount > 0) {
            return;
        }

        #check user status active or not
        if (!$this->user->status) {
            return;
        }

        # ==========================
        # add bulkexclusion to user, this is first time distribution feature , PLEASE PAY ATTENTION!
        # ==========================

        $allergyIds = [
            Allergy::ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_NOT_SEASONAL,
            Allergy::ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_BAKING_MIXES_AND_SPEC_PRODUCTS,
            Allergy::ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_SOY,
            Allergy::ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_PROTEIN_POWDER,
        ];

        $latestQuestionnaireAnswers = $this->user->latestQuestionnaireFullAnswers;
        if (!empty($latestQuestionnaireAnswers) && array_key_exists(QuestionnaireQuestionSlugsEnum::DIETS, $latestQuestionnaireAnswers)) {
            $questionnaireDiets = $latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::DIETS];

            if (!empty($questionnaireDiets) && is_array($questionnaireDiets)) {
                if (in_array(Diet::DIET_VEGAN, $questionnaireDiets)) {
                    $existsKey = array_search(Allergy::ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_SOY, $allergyIds);
                    if ($existsKey !== false) {
                        unset($allergyIds[$existsKey]);
                    }

                    $existsKey = array_search(Allergy::ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_PROTEIN_POWDER, $allergyIds);
                    if ($existsKey !== false) {
                        unset($allergyIds[$existsKey]);
                    }
                }

                if (in_array(Diet::DIET_AIP, $questionnaireDiets)) {
                    $existsKey = array_search(Allergy::ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_PROTEIN_POWDER, $allergyIds);
                    if ($existsKey !== false) {
                        unset($allergyIds[$existsKey]);
                    }
                }
            }
        }

        $this->user->bulkExclusions()->sync($allergyIds);
        $this->user->save();


        # =====================================
        # generate random valid recipes to user
        # =====================================

        // TODO:: review @NickMost
        $count = Calculation::recipeDistributionFirstTime($this->user);

        # ==============================
        # remove bulkexclusion from user,
        # ==============================
        // TODO:: @NickMost what if user really has this allergies???
        \DB::table('user_bulk_exclusions')
            ->where('user_id', $this->user->id)
            ->whereIn('allergy_id', $allergyIds)
            ->delete();

        // resync excluded ingredients
        SyncUserExcludedIngredientsJob::dispatchSync($this->user);

        // TODO:: review @NickMost probably 90 could be moved into configurations file
        //\Log::info('User added => '. $count .' recipes');
        if ($count < 90) {
            send_raw_admin_email(
                "User {$this->user->email} (#{$this->user->id}) has got less than 90 Recipes! User has got " . $count . ' recipes.',
                'Nutritionist needs to check'
            );
            return;
        }

        # ============================
        # generate recipe to Subscribe
        # ============================

        // TODO:: review @NickMost
        $result = Calculation::_generate2subscription($this->user);

        if (!$result['success']) {
            \Log::error($result['message']);
            return;
        }

        $now = Carbon::now();

        # message from email send
        $mailObject = new MailMailable('emails.newuserAutomated', ['userName' => $this->user->first_name]);

        $userEmail = $this->user->email;

        $mailObject->from(config('mail.from.address'), config('mail.from.name'))
            ->to($userEmail)
            //->bcc(config('mail.from.address')')
            ->subject('Dein Ernährungsplan ist fertig! / Your individual meal plan is ready for you!')
            ->onQueue('emails');

        \Mail::queue($mailObject);
        \Log::info('Automation finished for => ' . $this->user->id);

        $textEmail = 'Meal plan of user ' . $userEmail . ' (#' . $this->user->id . ') has been created by automation (' . $now . ')';

        \Mail::raw(
            $textEmail,
            static function ($message) {
                $message
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->to(config('mail.from.address'))
                    ->subject('Meal plan has been created by automation');
            }
        );
    }
}

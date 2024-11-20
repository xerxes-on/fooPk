<?php

namespace App\Services\Questionnaire\Converter\Create;

use App\Enums\FormularCreationMethodsEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Events\UserQuestionnaireChanged;
use App\Exceptions\PublicException;
use App\Http\Traits\Questionnaire\CanHandleQuestionnaireCreation;
use App\Models\Questionnaire;
use App\Models\User;
use App\Services\Questionnaire\Question\AllergiesQuestionService;
use App\Services\Questionnaire\Question\BaseQuestionService;
use App\Services\Questionnaire\QuestionnaireUserSession;
use DB;
use Illuminate\Support\Arr;
use Log;
use Modules\Ingredient\Services\UserExcludedIngredientsSyncService;

/**
 * Service to convert questionnaire from web source to constant one during create purpose (single time only).
 *
 * @package App\Services\Questionnaire\Converter\Create
 */
final class QuestionnaireCreateWEBConverterService
{
    use CanHandleQuestionnaireCreation;

    /**
     * Convert questionnaire from web user session to constant one and store.
     * @note Used to create questionnaire for first time users over web.
     * @throws \App\Exceptions\PublicException
     */
    public function convertFromWebSession(User $user): void
    {
        // uses when first time Questionnaire from web

        // TODO:: review @NickMost
        $userAnswers = session(QuestionnaireUserSession::SESSION_PREFIX . $user->id, []);

        if (empty($userAnswers)) {
            Log::error(
                'Questionnaire is no saved. answers missing',
                ['user_id' => $user->id, 'occurrence' => 'WEB creating start attempt']
            );
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }
        $dataToSave = [
            'user_id'         => $user->id,
            'is_approved'     => true,
            'creation_method' => FormularCreationMethodsEnum::FREE,
        ];
        $answersToSave = [];
        foreach ($userAnswers as $answer) {
            // we don`t use info page data

            $answerToSave = $answer['answer'];

            //	Admins must confirm formular manually in case diseases and allergy are set to other
            if (
                in_array(
                    $answer['slug'],
                    [QuestionnaireQuestionSlugsEnum::ALLERGIES, QuestionnaireQuestionSlugsEnum::DISEASES],
                    true
                ) &&
                array_key_exists(BaseQuestionService::OTHER_OPTION_SLUG, $answerToSave)
            ) {
                // Remove `other` option if it is empty
                array_walk($answerToSave, static function ($value, $key) use (&$answerToSave): void {
                    // Wrong interpretation of `other` option, data was sent as plain field with no users data
                    if (is_int($key) && $value === BaseQuestionService::OTHER_OPTION_SLUG) {
                        unset($answerToSave[$key]);
                        return;
                    }
                    if ($key !== BaseQuestionService::OTHER_OPTION_SLUG) {
                        return;
                    }
                    if (!empty($value)) {
                        return;
                    }
                    unset($answerToSave[BaseQuestionService::OTHER_OPTION_SLUG]);
                });
                // Prevent auto calculation ONLY if `other` option is not empty
                if (!empty($answerToSave[BaseQuestionService::OTHER_OPTION_SLUG])) {
                    $dataToSave['is_approved'] = false;
                }
            }

            // Format data for sports according to its keys
            if ($answer['slug'] === QuestionnaireQuestionSlugsEnum::SPORTS) {
                $answerToSave = Arr::mapWithKeys($answerToSave, static fn($value) => $value);
                // Ensure frequency is first then duration
                foreach ($answerToSave as $key => $value) {
                    uksort($value, static function ($a, $b): int {
                        if ($a === 'frequency') {
                            return -1;
                        }
                        if ($b === 'frequency') {
                            return 1;
                        }
                        return 0;
                    });
                    $answerToSave[$key] = $value;
                }
            }

            // Store first name separately
            if ($answer['slug'] === QuestionnaireQuestionSlugsEnum::FIRST_NAME) {
                $user->first_name = $answerToSave;
                $user->save();
            }

            // TODO:: review later, duplications on other places
            if ($answer['slug'] === QuestionnaireQuestionSlugsEnum::ALLERGIES && is_array($answerToSave)) {
                foreach ($answerToSave as $value) {
                    if (in_array($value, [AllergiesQuestionService::ANSWER_HIST, AllergiesQuestionService::ANSWER_OXALIC])) {
                        $dataToSave['is_approved'] = false;
                    }
                }
            }

            // Save answers
            $answersToSave[] = [
                'questionnaire_question_id' => $answer['question_id'],
                'answer'                    => $answerToSave,
            ];
        }

        if (empty($answersToSave)) {
            QuestionnaireUserSession::flushData($user->id);
            Log::error(
                'Questionnaire is no saved. answers missing',
                ['user_id' => $user->id, ['user_id' => $user->id, 'occurrence' => 'WEB creating before storing attempt']]
            );
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        $transactionStatus = true;
        try {
            DB::transaction(
                static function () use ($user, $dataToSave, $answersToSave) {
                    Questionnaire::create($dataToSave)->answers()->createMany($answersToSave);
                    app(UserExcludedIngredientsSyncService::class)->syncWithQuestionnaire($user, $answersToSave);
                },
                (int)config('database.transaction_attempts')
            );
        } catch (\Throwable $e) {
            logError($e, ['user_id' => $user->id, 'occurrence' => 'WEB creating after storing attempt']);
            $transactionStatus = false;
        }

        if (!$transactionStatus) {
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        UserQuestionnaireChanged::dispatch($user->id);

        # todo: check chargebee data
        # todo; perform calculations for dietdata if ($dataToSave[is_approved] === true) ->calcualte dietdata
        # todo: assign challenge (first time challenge) today (challenge related to chargebee plan id)
        # todo: start preliminary calculatiolns

        $this->processCreateEvent($user);

        # todo: recipeDistributionFirstTime
        # todo: $result = Calculation::_generate2subscription($this->user);
    }
}

<?php

namespace App\Services\Questionnaire\Converter\Create;

use App\Enums\FormularCreationMethodsEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionTypesEnum;
use App\Events\UserQuestionnaireChanged;
use App\Exceptions\PublicException;
use App\Http\Traits\Questionnaire\CanHandleQuestionnaireCreation;
use App\Models\Questionnaire;
use App\Models\QuestionnaireTemporary;
use App\Models\User;
use App\Services\Questionnaire\Question\AllergiesQuestionService;
use App\Services\Questionnaire\Question\BaseQuestionService;
use DB;
use Illuminate\Support\Arr;
use Log;
use Modules\Ingredient\Services\UserExcludedIngredientsSyncService;

/**
 * Service to convert questionnaire from api source to constant one during create purpose (single time only).
 *
 * @package App\Services\Questionnaire\Converter\Create
 */
final class QuestionnaireCreateAPIConverterService
{
    use CanHandleQuestionnaireCreation;

    /**
     * Convert questionnaire from temporarily to constant one and store.
     * @note Used to create questionnaire for first time users over api.
     * @throws \App\Exceptions\PublicException
     */
    public function convertFromTemporary(User $user, string $fingerprint): void
    {
        // first time creation
        $answers = QuestionnaireTemporary::whereFingerprint($fingerprint)->with('question')->get();

        if ($answers->isEmpty()) {
            Log::error(
                'Questionnaire is no saved. answers missing',
                ['user_id' => $user->id, 'fingerprint' => $fingerprint, 'occurrence' => 'Api creating start attempt']
            );
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        $dataToSave = [
            'user_id'         => $user->id,
            'is_approved'     => true,
            'creation_method' => FormularCreationMethodsEnum::FREE,
        ];
        $answersToSave = [];
        $answers->each(function (QuestionnaireTemporary $answer) use (&$answersToSave, &$dataToSave): void {
            // do not need to save any info page data
            if (in_array(
                $answer->question->type,
                [QuestionnaireQuestionTypesEnum::SALES_PAGE, QuestionnaireQuestionTypesEnum::INFO_PAGE],
                true
            )) {
                return;
            }

            // Case when data is missing or doesn't exist
            $answerToSave = $answer->answer[$answer->question->slug] ?? null;
            if (empty($answerToSave)) {
                return;
            }

            $questionWithOtherOption = in_array(
                $answer->question->slug,
                [QuestionnaireQuestionSlugsEnum::ALLERGIES, QuestionnaireQuestionSlugsEnum::DISEASES],
                true
            );

            // case when user has selected other option and then selected some other options
            if ($questionWithOtherOption && is_array($answerToSave)) {
                foreach ($answerToSave as $key => $value) {
                    if (is_array($value) && isset($value[BaseQuestionService::OTHER_OPTION_SLUG])) {
                        unset($answerToSave[$key]);
                        $answerToSave[BaseQuestionService::OTHER_OPTION_SLUG] = $value[BaseQuestionService::OTHER_OPTION_SLUG];
                    }
                }
            }

            //	Admins must confirm formular manually in case diseases and allergy are set to other
            if ($questionWithOtherOption &&
                (
                    in_array(BaseQuestionService::OTHER_OPTION_SLUG, $answer->answer[$answer->question->slug]) ||
                    array_key_exists(BaseQuestionService::OTHER_OPTION_SLUG, $answerToSave)
                )
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
            if ($answer->question->slug === QuestionnaireQuestionSlugsEnum::SPORTS) {
                $answerToSave = Arr::mapWithKeys($answer->answer[$answer->question->slug], static fn($value) => $value);
            }

            // TODO:: review later, duplications on other places
            if ($answer->question->slug === QuestionnaireQuestionSlugsEnum::ALLERGIES && is_array($answerToSave)) {
                foreach ($answerToSave as $value) {
                    if (in_array($value, [AllergiesQuestionService::ANSWER_HIST, AllergiesQuestionService::ANSWER_OXALIC])) {
                        $dataToSave['is_approved'] = false;
                    }
                }
            }

            // Save answers
            $answersToSave[] = [
                'questionnaire_question_id' => $answer->question->id,
                'answer'                    => $answerToSave,
            ];
        });

        if (empty($answersToSave)) {
            QuestionnaireTemporary::whereFingerprint($fingerprint)->delete();
            Log::error(
                'Questionnaire is no saved. answers missing',
                ['user_id' => $user->id, 'fingerprint' => $fingerprint, 'occurrence' => 'Api creating before storing attempt']
            );
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        $transactionStatus = true;
        try {
            DB::transaction(
                static function () use ($user, $fingerprint, $dataToSave, $answersToSave) {
                    QuestionnaireTemporary::whereFingerprint($fingerprint)->delete();
                    Questionnaire::create($dataToSave)->answers()->createMany($answersToSave);
                    app(UserExcludedIngredientsSyncService::class)->syncWithQuestionnaire($user, $answersToSave);
                },
                (int)config('database.transaction_attempts')
            );
        } catch (\Throwable $e) {
            logError($e, ['user_id' => $user->id, 'occurrence' => 'Api creating after storing attempt']);
            $transactionStatus = false;
        }

        if (!$transactionStatus) {
            QuestionnaireTemporary::whereFingerprint($fingerprint)->delete();
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        UserQuestionnaireChanged::dispatch($user->id);

        $this->processCreateEvent($user);

        # todo: recipeDistributionFirstTime

        # todo: $result = Calculation::_generate2subscription($this->user);
    }
}

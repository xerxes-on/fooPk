<?php

namespace App\Services\Questionnaire\Converter\Edit;

use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionTypesEnum;
use App\Events\UserQuestionnaireChanged;
use App\Exceptions\PublicException;
use App\Exceptions\Questionnaire\NoChangesMade;
use App\Http\Traits\Questionnaire\CanPrepareQuestionnaireEditData;
use App\Models\Questionnaire;
use App\Models\QuestionnaireTemporary;
use App\Models\User;
use App\Services\Questionnaire\Question\AllergiesQuestionService;
use App\Services\Questionnaire\Question\BaseQuestionService;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Log;
use Modules\Ingredient\Services\UserExcludedIngredientsSyncService;

/**
 * Service to convert questionnaire from api source to constant one during editing purpose.
 *
 * @package App\Services\Questionnaire\Converter\Edit
 */
final class QuestionnaireEditAPIConverterService extends QuestionnaireEditBaseConverter
{
    use CanPrepareQuestionnaireEditData;

    /**
     * Converting questionnaire from temporary editing to constant one and store.
     * @note Used to update questionnaire second and consecutive times for users over api.
     * TODO: Nick need to review it, need to add some automations and maybe check on diffs
     * @throws \App\Exceptions\Questionnaire\NoChangesMade
     * @throws \App\Exceptions\PublicException
     */
    public function convertFromTemporaryEditing(User $user): void
    {
        // TODO:: review @NickMost
        $answers = QuestionnaireTemporary::whereFingerprint($user->id)->with('question')->get();

        if ($answers->isEmpty()) {
            Log::error(
                'Questionnaire is no saved. Answers missing',
                ['user_id' => $user->id, 'occurrence' => 'Api editing start attempt']
            );
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        $dataToSave = [
            'user_id'     => $user->id,
            'is_approved' => true,
        ];
        $answersToSave = [];

        // Gather data from previous questionnaire to finds diffs
        $previousData = $this->gatherPreviousData($user);

        $answers->each(
            function (QuestionnaireTemporary $answer) use (&$answersToSave, &$dataToSave, &$previousData): void {
                // do not need to save any info page data
                if (in_array(
                    $answer->question->type,
                    [QuestionnaireQuestionTypesEnum::SALES_PAGE, QuestionnaireQuestionTypesEnum::INFO_PAGE],
                    true
                )) {
                    return;
                }

                $answerToSave            = $answer->answer[$answer->question->slug];
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
                    $answerToSave = Arr::mapWithKeys($answer->answer[$answer->question->slug], fn(mixed $value) => $value);
                }

                // TODO:: review later, duplications on other places
                if ($answer->question->slug === QuestionnaireQuestionSlugsEnum::ALLERGIES && is_array($answerToSave)) {
                    foreach ($answerToSave as $value) {
                        if (in_array($value, [AllergiesQuestionService::ANSWER_HIST, AllergiesQuestionService::ANSWER_OXALIC])) {
                            $dataToSave['is_approved'] = false;
                        }
                    }
                }

                // gather all new answers to compare with old ones
                $previousData[$answer->question->slug]['new_answer'] = $answerToSave;
                $oldAnswer                                           = $previousData[$answer->question->slug]['answer'] ?? null;
                // Find difference between new and old answers
                if (is_string($answerToSave) && $oldAnswer == $answerToSave) {
                    unset($previousData[$answer->question->slug]);
                }
                if (is_array($answerToSave)) {
                    $isList = array_is_list($answerToSave);
                    if (empty($answerToSave) && empty($oldAnswer)) {
                        unset($previousData[$answer->question->slug]);
                    } elseif ($isList && [] === array_diff($answerToSave, (array)$oldAnswer)) {
                        unset($previousData[$answer->question->slug]);
                    } elseif (!$isList &&
                        $answer->question->slug === QuestionnaireQuestionSlugsEnum::SPORTS &&
                        [] === array_diff_key($answerToSave, (array)$oldAnswer)
                    ) {
                        unset($previousData[$answer->question->slug]);
                    }
                }

                // Save answers
                $answersToSave[] = [
                    'questionnaire_question_id' => $answer->question->id,
                    'answer'                    => $answerToSave,
                ];
            }
        );

        // Stop here if no changes were made
        if ($previousData === []) {
            throw new NoChangesMade('Nothing was changed!');
        }

        //	clean up previous data as there can be messy data occasionally
        foreach (array_keys($previousData) as $key) {
            if (!isset($previousData[$key]['new_answer'])) {
                unset($previousData[$key]);
            }
        }

        $dataToSave['creation_method'] = $this->setQuestionnaireCreationMethod($user);

        // WEB-582
        if (!$user->calc_auto) {
            $dataToSave['is_approved'] = false;
        }

        if (empty($answersToSave)) {
            QuestionnaireTemporary::whereFingerprint($user->id)->delete();
            Log::error(
                'Questionnaire is no saved. answers missing',
                ['user_id' => $user->id, 'occurrence' => 'Api editing before storing attempt']
            );
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        $transactionStatus = true;
        try {
            DB::transaction(
                static function () use ($user, $dataToSave, $answersToSave): void {
                    QuestionnaireTemporary::whereFingerprint($user->id)->delete();
                    Questionnaire::create($dataToSave)->answers()->createMany($answersToSave);
                    app(UserExcludedIngredientsSyncService::class)->syncWithQuestionnaire($user, $answersToSave);
                },
                (int)config('database.transaction_attempts')
            );
        } catch (\Throwable $e) {
            logError($e, ['user_id' => $user->id, 'occurrence' => 'Api editing after storing attempt']);
            $transactionStatus = false;
        }

        if (!$transactionStatus) {
            QuestionnaireTemporary::whereFingerprint($user->id)->delete();
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        UserQuestionnaireChanged::dispatch($user->id);
        $this->processEditingEvent($user);
    }

    private function gatherPreviousData(User $user): array
    {
        try {
            return Arr::mapWithKeys(
                $this->prepareQuestionnaireEditData($user, request()),
                static function ($item): array {
                    $answer = $item['answer'];
                    // We only need to reformat excluded ingredients as they are prepared in a different way
                    if ($item['slug'] === QuestionnaireQuestionSlugsEnum::EXCLUDE_INGREDIENTS && !empty($answer)) {
                        $answer = Arr::pluck($item['answer'], 'key');
                    }
                    return [$item['slug'] => ['answer' => $answer]];
                }
            );
        } catch (ModelNotFoundException) {
            return [];
        }
    }
}

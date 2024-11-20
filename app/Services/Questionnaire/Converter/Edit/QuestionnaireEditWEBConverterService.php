<?php

namespace App\Services\Questionnaire\Converter\Edit;

use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Events\UserQuestionnaireChanged;
use App\Exceptions\PublicException;
use App\Models\Questionnaire;
use App\Models\User;
use App\Services\Questionnaire\Question\AllergiesQuestionService;
use App\Services\Questionnaire\Question\BaseQuestionService;
use DB;
use Log;
use Modules\Ingredient\Services\UserExcludedIngredientsSyncService;

/**
 * Service to convert questionnaire from web source to constant one during editing purpose.
 *
 * @package App\Services\Questionnaire\Converter\Edit
 */
final class QuestionnaireEditWEBConverterService extends QuestionnaireEditBaseConverter
{
    /**
     * Convert questionnaire from web to constant one and store.
     * @note Used to create questionnaire second and consecutive times for users over web.
     * @note generator will not help in speeding up the answer processing. Was tested already
     * @throws PublicException
     * @throws \Exception
     */
    public function convertFromWeb(User $user, array $answers, ?int $creator = null): void
    {
        // TODO:: review @NickMost
        $dataToSave = [
            'user_id'     => $user->id,
            'is_approved' => true,
            'creator_id'  => $creator,
        ];
        $answersToSave = [];
        foreach ($answers as $answer) {
            switch ($answer['slug']) {
                case QuestionnaireQuestionSlugsEnum::EXTRA_GOAL:
                case QuestionnaireQuestionSlugsEnum::DIETS:
                    $answerToSave = array_values($answer['answers']);
                    break;
                case QuestionnaireQuestionSlugsEnum::ALLERGIES:
                case QuestionnaireQuestionSlugsEnum::DISEASES:
                    $deceases = [];
                    foreach ($answer['answers'] as $key => $value) {
                        if ($key === BaseQuestionService::OTHER_OPTION_SLUG && !empty($value)) {
                            $dataToSave['is_approved']                        = false;
                            $deceases[BaseQuestionService::OTHER_OPTION_SLUG] = $value;
                            continue;
                        }
                        // TODO:: review later, duplications on other places
                        if (in_array($value, [AllergiesQuestionService::ANSWER_HIST, AllergiesQuestionService::ANSWER_OXALIC])) {
                            $dataToSave['is_approved'] = false;
                        }
                        // Ensure we dont save empty values
                        if (!empty($value)) {
                            $deceases[] = $value;
                        }
                    }
                    $answerToSave = $deceases;
                    break;
                default:
                    $answerToSave = $answer['answers'];
            }
            $answersToSave[] = [
                'questionnaire_question_id' => $answer['id'],
                'answer'                    => $answerToSave
            ];
        }

        if (empty($answersToSave)) {
            Log::error(
                'Questionnaire is no saved. answers missing',
                ['user_id' => $user->id, 'occurrence' => 'WEB editing before storing attempt']
            );
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        $dataToSave['creation_method'] = $this->setQuestionnaireCreationMethod($user);

        // WEB-582
        if (!$user->calc_auto) {
            $dataToSave['is_approved'] = false;
        }

        $transactionStatus = true;
        try {
            DB::transaction(
                static function () use ($user, $dataToSave, $answersToSave) {
                    Questionnaire::create($dataToSave)->answers()->createMany($answersToSave);
                    app(UserExcludedIngredientsSyncService::class)->syncWithQuestionnaire($user, $answersToSave);
                }
            );
        } catch (\Throwable $e) {
            logError($e, ['user_id' => $user->id, 'occurrence' => 'WEB editing after storing attempt']);
            $transactionStatus = false;
        }

        if (!$transactionStatus) {
            throw new PublicException('Questionnaire cannot be saved. Please try again.');
        }

        UserQuestionnaireChanged::dispatch($user->id);
        $this->processEditingEvent($user, !$creator);
    }
}

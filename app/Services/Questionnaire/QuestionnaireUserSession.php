<?php

namespace App\Services\Questionnaire;

use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Service to track user's temporarily questionnaire answers.
 *
 * @package App\Services\Questionnaire
 */
final class QuestionnaireUserSession
{
    public const SESSION_PREFIX = 'questionnaire_of_user_';

    /**
     * @throws ModelNotFoundException
     */
    public function getLastQuestion(int $userId): QuestionnaireQuestion
    {
        $userAnswers    = session(self::SESSION_PREFIX . $userId, []);
        $lastQuestionId = count($userAnswers) ? max(array_keys($userAnswers)) : null;
        return QuestionnaireQuestion::findOrFail($lastQuestionId);
    }

    public function updateOrCreateQuestionRecord(int $questionId, int $userId, array|string $answer = null): void
    {
        $sessionData = session(self::SESSION_PREFIX . $userId, []);
        $answer      = json_decode($answer, true);
        $slug        = array_keys($answer)[0];

        // if answer includes other save answer with different format
        if (is_array($answer[$slug])) {
            foreach ($answer[$slug] as $key => $value) {
                if (is_array($value) && isset($value['other'])) {
                    $answer[$slug]['other'] = $value['other'];
                    unset($answer[$slug][$key]);
                }
            }
        }

        $sessionData[$questionId] = [
            'question_id' => $questionId,
            'slug'        => $slug,
            'answer'      => $answer[$slug],
        ];

        session([self::SESSION_PREFIX . $userId => $sessionData]);
    }

    public function getAnswersByQuestionIds(array|int $questionIds, int $userId): array|string|null
    {
        $userAnswers     = session(self::SESSION_PREFIX . $userId, []);
        $filteredAnswers = [];

        if (is_array($questionIds)) {
            foreach ($userAnswers as $answer => $answerDetails) {
                if (in_array($answer, $questionIds) && isset($answerDetails['answer'])) {
                    $filteredAnswers = array_merge($filteredAnswers, (array)$answerDetails['answer']);
                }
            }
        }

        if (is_integer($questionIds)) {
            foreach ($userAnswers as $answer => $answerDetails) {
                if ($questionIds === $answer && isset($answerDetails['answer'])) {
                    $filteredAnswers[$answerDetails['slug']] = $answerDetails['answer'];
                }
            }
        }

        return $filteredAnswers;
    }

    public static function flushData(int $userId): void
    {
        session()->forget(self::SESSION_PREFIX . $userId);
    }
}

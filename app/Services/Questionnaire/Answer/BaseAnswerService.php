<?php

namespace App\Services\Questionnaire\Answer;

use App\Contracts\Services\Questionnaire\AnswerInterface;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireTemporary;
use App\Models\User;
use App\Services\Questionnaire\QuestionnaireUserSession;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Base answer service class.
 *
 * @package App\Services\Questionnaire\Answer
 */
abstract class BaseAnswerService implements AnswerInterface
{
    public function getAnswer(): string|array|null
    {
        return $this->questionAnswer[$this->questionModel->slug] ?? ($this->questionAnswer ?? null);
    }

    public function getFormattedAnswer(): string
    {
        if (empty($this->questionAnswer)) {
            return '';
        }

        if (!is_array($this->questionAnswer)) {
            return trans_fb(
                "questionnaire.questions.{$this->questionModel->slug}.options.$this->questionAnswer",
                $this->questionAnswer,
                $this->locale
            );
        }

        $return = '';
        foreach ($this->questionAnswer as $answer) {
            if (is_array($answer)) {
                $return .= ', ';
                continue;
            }
            $return .= trans_fb(
                "questionnaire.questions.{$this->questionModel->slug}.options.$answer",
                locale: $this->locale
            ) . ', ';
        }
        return trim($return, ', ');
    }

    public function reformatAnswerFromApi(string $answer): string
    {
        return $answer;
    }

    public function reformatAnswerFromWeb(null|string|array $answer): string|array
    {
        return is_null($answer) ? '' : $answer;
    }

    /**
     * Try to obtain answer by identifier.
     * String identifier - usually a fingerprint for temporarily questionnaire.
     * Integer identifier - usually a users ID.
     */
    public function tryToGetAnswerByIdentity(string|int|null $identity = null): string|array|null
    {
        return is_null($identity) ?
            null :
            match ($this->questionnaireType) {
                QuestionnaireSourceRequestTypesEnum::WEB => app(QuestionnaireUserSession::class)
                    ->getAnswersByQuestionIds($this->questionModel->id, (int)$identity),

                QuestionnaireSourceRequestTypesEnum::API => QuestionnaireTemporary::whereFingerprint($identity)
                    ->whereQuestionnaireQuestionId(
                        $this->questionModel->id
                    )
                    ->first('answer')?->answer,

                QuestionnaireSourceRequestTypesEnum::API_EDITING => $this->getAnswerForApiEditing(
                    $this->questionModel->id,
                    (int)$identity
                ),
                default => null
            };
    }

    public function getAnswerForApiEditing(int $questionId, int $userId): string|array|null
    {
        try {
            return QuestionnaireTemporary::whereFingerprint($userId)
                ->whereQuestionnaireQuestionId($questionId)
                ->firstOrFail()->answer;
        } catch (ModelNotFoundException) {
            // get latest questionnaire id
            $lastQuestionnaireId = User::find($userId)?->latestQuestionnaire()?->first()?->id;

            return QuestionnaireAnswer::whereQuestionnaireId($lastQuestionnaireId)
                ->whereQuestionnaireQuestionId($questionId)
                ->first()?->answer;
        }
    }
}

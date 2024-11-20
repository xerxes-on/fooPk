<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Exceptions\Questionnaire\QuestionValidation;
use App\Http\Traits\Questionnaire\CanReformatNumericValues;

/**
 * Service responsible for handling question about client weight goal.
 *
 * @package App\Services\Questionnaire\Question
 */
final class WeightGoalQuestionService extends BaseValidationRequireQuestionService
{
    use CanReformatNumericValues;

    public function getVariations(): array
    {
        return [
            'weight_goal'
        ];
    }

    public function validateOverApi(string $answer): bool
    {
        try {
            $parsedAnswer = json_decode($answer, true);
            $this->validateAnswerStructureForSlug($parsedAnswer);

            $parsedAnswer = str_contains((string)$parsedAnswer[$this->questionModel->slug], '.') ?
                (float)$parsedAnswer[$this->questionModel->slug] :
                (int)$parsedAnswer[$this->questionModel->slug];

            // If answer is 0, it means that user wants to skip this question, it's not a required question.
            if ($parsedAnswer === 0) {
                return true;
            }

            if (!($parsedAnswer >= 40 && $parsedAnswer <= 200)) {
                throw new QuestionValidation(
                    trans("questionnaire.questions.{$this->questionModel->slug}.validation_error", locale: $this->locale)
                );
            }
        } catch (QuestionValidation $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public function validateOverWeb(string|array $answer): bool
    {
        try {
            $parsedAnswer = str_contains($answer, '.') ? (float)$answer : (int)$answer;
            // If answer is 0, it means that user wants to skip this question, it's not a required question.
            if ($parsedAnswer === 0) {
                return true;
            }
            if (!($parsedAnswer >= 40 && $parsedAnswer <= 200)) {
                throw new QuestionValidation(
                    trans("questionnaire.questions.{$this->questionModel->slug}.validation_error", locale: $this->locale)
                );
            }
        } catch (QuestionValidation $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public function getFormattedAnswer(): string
    {
        return empty($this->questionAnswer) ? '' : "$this->questionAnswer kg";
    }
}

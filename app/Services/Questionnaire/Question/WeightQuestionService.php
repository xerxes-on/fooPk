<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Exceptions\Questionnaire\QuestionValidation;
use App\Http\Traits\Questionnaire\CanReformatNumericValues;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service responsible for handling question related to clients weight.
 *
 * @package App\Services\Questionnaire\Question
 */
final class WeightQuestionService extends BaseValidationRequireQuestionService
{
    use CanReformatNumericValues;

    public function getVariations(): array
    {
        return [
            'weight'
        ];
    }

    public function validateOverApi(string|int $answer): bool
    {
        try {
            $parsedAnswer = json_decode($answer, true);
            $this->validateAnswerStructureForSlug($parsedAnswer);
            if (!auth()->check()) {
                \App::setLocale($this->locale);
            }
            Validator::make(
                $parsedAnswer,
                [
                    'weight' => ['required', 'numeric', 'min:40', 'max:200'],
                ],
                [
                    'min' => trans('questionnaire.validation.weight.min', locale: $this->locale),
                    'max' => trans('questionnaire.validation.weight.max', locale: $this->locale),
                ]
            )
                ->validate();
        } catch (ValidationException|QuestionValidation $e) {
            $this->validationMessage = trim(preg_replace("/\([^)]+\)/", "", $e->getMessage()));
            return false;
        }

        return true;
    }

    public function validateOverWeb(string|array $answer): bool
    {
        try {
            Validator::make(
                [$this->questionModel->slug => $answer],
                [
                    'weight' => ['required', 'numeric', 'min:40', 'max:200'],
                ]
            )
                ->validate();
        } catch (ValidationException $e) {
            $this->validationMessage = trim(preg_replace("/\([^)]+\)/", "", $e->getMessage()));
            return false;
        }

        return true;
    }

    public function getFormattedAnswer(): string
    {
        return empty($this->questionAnswer) ? '' : "$this->questionAnswer kg";
    }
}

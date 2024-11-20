<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Exceptions\Questionnaire\QuestionValidation;
use App\Http\Traits\Questionnaire\CanReformatNumericValues;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service responsible for handling question related to clients height.
 *
 * @package App\Services\Questionnaire\Question
 */
final class HeightQuestionService extends BaseValidationRequireQuestionService
{
    use CanReformatNumericValues;

    public function getVariations(): array
    {
        return [
            'height'
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
                    'height' => ['required', 'numeric', 'min:100', 'max:250']
                ],
                [
                    'min' => trans('questionnaire.validation.height.min', locale: $this->locale),
                    'max' => trans('questionnaire.validation.height.max', locale: $this->locale),
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
                    'height' => ['required', 'numeric', 'min:100', 'max:250']
                ]
            )
                ->validate();
        } catch (ValidationException $e) {
            $this->validationMessage = trim(preg_replace("/\([^)]+\)/", "", $e->getMessage()));
            return false;
        }

        return true;
    }
}

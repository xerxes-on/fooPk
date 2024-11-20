<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Exceptions\Questionnaire\QuestionValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service responsible for handling question about clients name.
 *
 * @package App\Services\Questionnaire\Question
 */
final class FirstNameQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'first_name'
        ];
    }

    public function reformatAnswerFromApi(string $answer): string
    {
        // the only thing that should be reformatted is the answer with key OTHER as it may contain special symbols
        return sanitize_string(remove_emoji($answer));
    }

    public function validateOverApi(string $answer): bool
    {
        try {
            $parsedAnswer = (array)json_decode($answer, true);
            $this->validateAnswerStructureForSlug($parsedAnswer);
            if (!auth()->check()) {
                \App::setLocale($this->locale);
            }
            Validator::make(
                $parsedAnswer,
                [
                    'first_name' => ['required', 'string', 'min:2'],
                ]
            )
                ->validate();
        } catch (ValidationException|QuestionValidation $e) {
            $this->validationMessage = trim((string)preg_replace("/\([^)]+\)/", "", $e->getMessage()));
            return false;
        }

        return true;
    }
}

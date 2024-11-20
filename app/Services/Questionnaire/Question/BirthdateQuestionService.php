<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Exceptions\Questionnaire\QuestionValidation;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service responsible for handling question related to clients birthdate.
 *
 * @package App\Services\Questionnaire\Question
 */
final class BirthdateQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'birthdate'
        ];
    }

    public function validateOverApi(string $answer): bool
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
                    'birthdate' => ['required', 'date_format:d.m.Y'],
                ]
            )
                ->validate();

            $date       = Carbon::parse($parsedAnswer[$this->questionModel->slug]);
            $currentAge = $date->diffInYears(Carbon::now());
            $errorKey   = match (true) {
                $currentAge < 16  => 'min_age',
                $currentAge > 100 => 'max_age',
                default           => '',
            };

            if ($errorKey !== '') {
                throw new QuestionValidation(
                    trans(
                        "questionnaire.questions.{$this->questionModel->slug}.validation_errors.$errorKey",
                        locale: $this->locale
                    )
                );
            }
        } catch (ValidationException|InvalidFormatException|QuestionValidation $e) {
            $this->validationMessage = trim(preg_replace("/\([^)]+\)/", "", $e->getMessage()));
            return false;
        }

        return true;
    }

    public function validateOverWeb(string|array $answer): bool
    {
        try {
            $date       = Carbon::parse($answer);
            $currentAge = $date->diffInYears(Carbon::now());
            $errorKey   = match (true) {
                $currentAge < 16  => 'min_age',
                $currentAge > 100 => 'max_age',
                default           => '',
            };

            if ($errorKey !== '') {
                throw new QuestionValidation(
                    trans(
                        "questionnaire.questions.{$this->questionModel->slug}.validation_errors.$errorKey",
                        locale: $this->locale
                    )
                );
            }
        } catch (InvalidFormatException|QuestionValidation $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public function reformatAnswerFromWeb(null|string|array $answer): string|array
    {
        if (is_array($answer)) {
            $answer = null;
        }
        return Carbon::parse($answer)->format('d.m.Y');
    }


    public function getFormattedAnswer(): string
    {
        $return = '';

        if (empty($this->questionAnswer)) {
            return $return;
        }

        try {
            $dateOfBirth = Carbon::parse($this->questionAnswer);
            $return      = $dateOfBirth->format('d.m.Y') . ' (' . $dateOfBirth->age . ')';
        } catch (InvalidFormatException $e) {
            logError($e);
        }

        return $return;
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Exceptions\Questionnaire\QuestionValidation;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\QuestionnaireUserSession;
use Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service responsible for handling question related to client sports activity.
 *
 * @package App\Services\Questionnaire\Question
 */
final class SportsQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'easy',
            '_easy_frequency',
            '_easy_duration',
            'medium',
            '_medium_frequency',
            '_medium_duration',
            'intensive',
            '_intensive_frequency',
            '_intensive_duration',
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
                Arr::mapWithKeys($parsedAnswer[$this->questionModel->slug], fn($value) => $value),
                [
                    'easy'                => ['nullable', 'array', 'min:2', 'max:2'],
                    'easy.frequency'      => ['numeric', 'min:1', 'max:7'],
                    'easy.duration'       => ['numeric', 'min:1', 'max:120'],
                    'medium'              => ['nullable', 'array', 'min:2', 'max:2'],
                    'medium.frequency'    => ['numeric', 'min:1', 'max:7'],
                    'medium.duration'     => ['numeric', 'min:1', 'max:120'],
                    'intensive'           => ['nullable', 'array', 'min:2', 'max:2'],
                    'intensive.frequency' => ['numeric', 'min:1', 'max:7'],
                    'intensive.duration'  => ['numeric', 'min:1', 'max:120'],
                ],
                [
                    '*.frequency' => trans('questionnaire.questions.sports.validation_errors.frequency', locale: $this->locale),
                    '*.duration'  => trans('questionnaire.questions.sports.validation_errors.duration', locale: $this->locale)
                ]
            )
                ->validate();
        } catch (ValidationException|QuestionValidation $e) {
            $this->validationMessage = trim(preg_replace("/\([^)]+\)/", "", $e->getMessage()));
            return false;
        }

        return true;
    }

    public function reformatAnswerFromWeb(null|string|array $answer): array
    {
        $return = [];
        if (is_null($answer) || is_string($answer)) {
            return $return;
        }
        foreach ($answer as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            foreach ($value as $subKey => $subValue) {
                if (empty($subValue)) {
                    continue;
                }
                $return[$key][$subKey] = $subValue;
            }
        }

        return $return;
    }

    public function getFormattedAnswer(): string
    {
        if (empty($this->questionAnswer)) {
            return '';
        }

        $return = '';
        foreach ($this->questionAnswer as $key => $answer) {
            $return .= trans_fb(
                "questionnaire.questions.{$this->questionModel->slug}.formatted_answer",
                '',
                $this->locale,
                [
                        'type'      => ucfirst((string)$key),
                        'frequency' => $answer['frequency'] ?? 0,
                        'duration'  => $answer['duration'] ?? 0
                    ]
            ) . '; ';
        }
        return trim($return, '; ');
    }

    protected function prepareOptions(): array
    {
        return Arr::mapWithKeys(
            $this->getVariations(),
            function (string $item): array {
                $key = $item;
                if (str_contains($item, '_')) {
                    $key = preg_replace('/\A_(.+)_/', '', $item);
                }
                return [
                    $item => trans(
                        "questionnaire.questions.{$this->questionModel->slug}.options.$key",
                        locale: $this->locale
                    )
                ];
            }
        );
    }

    public function definePreviousQuestion(Model $model, int|string $identifier): QuestionnaireQuestion
    {
        $query = QuestionnaireQuestion::query();

        $answer = match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::API => QuestionnaireTemporary::whereFingerprint($identifier)
                ->whereQuestionnaireQuestionId(
                    QuestionnaireQuestionIDEnum::MAIN_GOAL->value
                )
                ->first('answer')?->answer,

            QuestionnaireSourceRequestTypesEnum::WEB => tap(
                app(QuestionnaireUserSession::class)
                    ->getAnswersByQuestionIds(QuestionnaireQuestionIDEnum::MAIN_GOAL->value, (int)$identifier),
                fn(string|array|null $answer) => $query->forWeb()
            ),

            QuestionnaireSourceRequestTypesEnum::API_EDITING => tap(
                $this->getAnswerForApiEditing(QuestionnaireQuestionIDEnum::MAIN_GOAL->value, (int)$identifier),
                fn(string|array|null $answer) => $query->baseOnly()
            )
        };

        return match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::API => !in_array('lose_weight', array_values($answer), true)
                ? $query->active()->whereOrder($this->questionModel->order - 2)->firstOrFail()
                : $query->previousActive($this->questionModel->order)->firstOrFail(),
            default => $query->previousActive($this->questionModel->order)->firstOrFail(),
        };
    }
}

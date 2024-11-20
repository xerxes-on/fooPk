<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireTemporary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service responsible for handling question gathering client email and marketing subscription agreement.
 *
 * @package App\Services\Questionnaire\Question
 */
final class EmailQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'email',
            'subscribe_checkbox'
        ];
    }

    public function reformatAnswerFromApi(string $answer): string
    {
        // sanitize the string from excessive characters and finally remove the spaces
        return str_ireplace(' ', '', sanitize_string($answer));
    }

    public function validateOverApi(string $answer): bool
    {
        try {
            if (!auth()->check()) {
                \App::setLocale($this->locale);
            }

            Validator::make(
                (array)json_decode($answer, true) ?? [],
                [
                    'email' => [
                        'required',
                        'email:strict,dns,spoof,filter,filter_unicode',
                        'unique:App\Models\User,email'
                    ],
                    'subscribe_checkbox' => ['required', 'boolean'],
                ],
                ['email.unique' => trans('questionnaire.validation.email.unique', locale: $this->locale)]
            )
                ->validate();
        } catch (ValidationException $e) {
            $this->validationMessage = trim(preg_replace("/\([^)]+\)/", "", $e->getMessage()));
            return false;
        }

        return true;
    }

    public function defineNextQuestion(Model $model, int|string $identifier): QuestionnaireQuestion
    {
        $answer = null;
        if ($model::class === QuestionnaireTemporary::class) {
            $answer = $model::whereFingerprint($identifier)
                ->whereQuestionnaireQuestionId(QuestionnaireQuestionIDEnum::MAIN_GOAL->value)
                ->first()?->answer;
        }
        $answer = $answer ?? [];

        if (in_array('lose_weight', array_values($answer), true)) {
            return QuestionnaireQuestion::nextActive($this->questionModel->order)->firstOrFail();
        }

        // We need to skip weight goal question if user wants to gain/loose weight|TODO: make it more dynamic
        return QuestionnaireQuestion::whereOrder($this->questionModel->order + 2)->firstOrFail();
        // TODO: also can be later obtained from Questionnaire model...so need to take into account
    }
}

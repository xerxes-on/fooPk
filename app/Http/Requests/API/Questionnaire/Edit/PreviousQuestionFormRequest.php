<?php

namespace App\Http\Requests\API\Questionnaire\Edit;

use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request responsible for getting previous question from questionnaire while editing.
 *
 * @property int $question_id
 * @property \App\Models\User $user
 * @property-read QuestionnaireQuestion $currentQuestion
 *
 * @used-by \App\Http\Controllers\API\Questionnaire\QuestionnaireAPIController::goToPreviousQuestion()
 * @package App\Http\Requests\Questionnaire
 */
final class PreviousQuestionFormRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user' => $this->user(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $currentQuestion = QuestionnaireQuestion::whereId($this->question_id)->firstOrFail();
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('question_id', 'No previous question found');
                    return;
                }

                $this->merge([
                    'currentQuestion' => $currentQuestion,
                ]);
            }
        ];
    }
}

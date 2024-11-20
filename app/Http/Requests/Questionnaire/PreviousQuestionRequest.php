<?php

namespace App\Http\Requests\Questionnaire;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request responsible for getting previous question from questionnaire.
 *
 * @property int $question_id
 *
 * @property-read QuestionnaireQuestion $currentQuestion
 *
 * @package App\Http\Requests\Questionnaire\Web
 */
final class PreviousQuestionRequest extends FormRequest
{
    // TODO: need to think about proper authorization
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer'],
        ];
    }

    /**
     * Add an after validation callback.
     */
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

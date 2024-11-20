<?php

namespace App\Http\Requests\API\Questionnaire\Create;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request responsible for getting previous question from questionnaire.
 *
 * @property string $lang
 * @property string $fingerprint
 * @property int $question_id
 *
 * @property-read QuestionnaireQuestion $currentQuestion
 *
 * @package App\Http\Requests\Questionnaire
 */
final class PreviousQuestionFormRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'lang'        => ['required', 'string', 'in:en,de'],
            'fingerprint' => ['required', 'string'],
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

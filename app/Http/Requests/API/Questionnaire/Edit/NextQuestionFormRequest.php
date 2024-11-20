<?php

namespace App\Http\Requests\API\Questionnaire\Edit;

use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request responsible for validating & updating question answers while editing.
 *
 * @property \App\Models\User $user
 * @property int $question_id
 * @property string|null $answer
 * @property-read QuestionnaireQuestion $question
 *
 * @used-by \App\Http\Controllers\API\Questionnaire\QuestionnaireAPIController::updateAndProceed()
 * @package App\Http\Requests\Questionnaire
 */
final class NextQuestionFormRequest extends FormRequest
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
            'answer'      => ['nullable', 'string'],
        ];
    }


    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $question = QuestionnaireQuestion::whereId($this->question_id)->firstOrFail();
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('question_id', 'Question not found');
                    return;
                }

                if ($question->is_required && empty($this->answer)) {
                    $validator
                        ->errors()
                        ->add(
                            'answer',
                            trans('questionnaire.validation.answer.missing', locale: $this->user->lang)
                        );
                    return;
                }

                try {
                    /**    @var \App\Services\Questionnaire\Question\BaseQuestionService $service */
                    $service = new $question->service(
                        $question,
                        $this->user->lang,
                        $this->user->id,
                        QuestionnaireSourceRequestTypesEnum::API_EDITING
                    );
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('question_id', 'Question service not found');
                    return;
                }

                $answer = $service->reformatAnswerFromApi(is_null($this->answer) ? '' : $this->answer);

                // need specific validation depending on the service
                if ($service->mustBeValidated() && !$service->validateOverApi($answer)) {
                    $validator->errors()->add('answer', $service->getValidationErrorMessage());
                    return;
                }

                $this->merge([
                    'question' => $question,
                    'answer'   => $answer,
                ]);
            }
        ];
    }

}

<?php

namespace App\Http\Requests\API\Questionnaire\Create;

use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request responsible for validating & saving question answer.
 *
 * @property string $lang
 * @property string $fingerprint
 * @property int $question_id
 * @property string|null $answer
 * @property-read QuestionnaireQuestion $question
 *
 * @used-by \App\Http\Controllers\API\Questionnaire\QuestionnaireAnonymousAPIController::saveAndProceed()
 * @package App\Http\Requests\Questionnaire
 */
final class SaveAnswerFormRequest extends FormRequest
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
                    $validator->errors()->add('answer', trans('questionnaire.validation.answer.missing', locale: $this->lang));
                    return;
                }

                try {
                    $service = new $question->service(
                        $question,
                        $this->lang,
                        $this->fingerprint,
                        QuestionnaireSourceRequestTypesEnum::API
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

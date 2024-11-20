<?php

namespace App\Http\Requests\Questionnaire;

use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request responsible for validating & saving question answer.
 *
 * @property int $question_id
 * @property string|null $answer
 * @property-read QuestionnaireQuestion $question
 *
 * @package App\Http\Requests\Questionnaire\Web
 */
final class NextQuestionRequest extends FormRequest
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
            'answer'      => ['nullable', 'string'],
        ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = $this->user();
                $lang = $user?->lang ?? 'de';
                try {
                    $question = QuestionnaireQuestion::whereId($this->question_id)->firstOrFail();
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('question_id', 'Question not found');
                    return;
                }

                if ($question->is_required && empty($this->answer)) {
                    $validator->errors()->add('answer', trans('questionnaire.validation.answer.missing', locale: $lang));
                    return;
                }

                try {
                    $service = new $question->service(
                        $question,
                        $lang,
                        $user?->id,
                        QuestionnaireSourceRequestTypesEnum::WEB
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

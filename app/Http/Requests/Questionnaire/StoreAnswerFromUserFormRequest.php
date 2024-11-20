<?php

namespace App\Http\Requests\Questionnaire;

use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\QuestionnaireQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request responsible for validating & saving question answer.
 *
 * @property int $client_id
 * @property string $main_goal
 * @property numeric $weight_goal
 * @property array $extra_goal
 * @property string $lifestyle
 * @property array $diets
 * @property string $meals_per_day
 * @property array $allergies
 * @property array $exclude_ingredients
 * @property array $sports
 * @property string $recipe_preferences
 * @property array $diseases
 * @property string $gender
 * @property string $birthdate
 * @property string|int $height
 * @property string|int $weight
 * @property string $fat_content
 *
 * @property-read User $client
 * @property-read QuestionnaireQuestion $question
 * @property-read array $answers
 *
 * @used-by \App\Http\Controllers\QuestionnaireController::store()
 * @used-by \App\Admin\Http\Controllers\ClientQuestionnaireAdminController::store()
 *
 * @package App\Http\Requests\Questionnaire
 */
final class StoreAnswerFromUserFormRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'client_id'           => ['required', 'int'],
            'main_goal'           => ['required', 'string'],
            'weight_goal'         => ['nullable', 'numeric'],
            'extra_goal'          => ['array'],
            'lifestyle'           => ['required', 'string'],
            'diets'               => ['required', 'array'],
            'meals_per_day'       => ['required', 'string'],
            'allergies'           => ['array'],
            'exclude_ingredients' => ['array'],
            'sports'              => ['array'],
            'diseases'            => ['array'],
            'gender'              => ['required', 'string'],
            'birthdate'           => ['required', 'date_format:d.m.Y'],
            'height'              => ['required', 'numeric'],
            'weight'              => ['required', 'numeric'],
            'fat_content'         => ['string']
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
                    $client = User::findOrFail($this->client_id);
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('client_id', 'Client not found');
                    return;
                }

                $questionSlugs = array_keys($this->except(['_token', 'client_id']));
                $questions     = QuestionnaireQuestion::whereIn('slug', $questionSlugs)->get();
                $answers       = [];
                $questions->each(function (QuestionnaireQuestion $question) use (&$validator, &$answers) {
                    try {
                        $service = new $question->service(
                            $question,
                            $this->user()->lang,
                            questionnaireType: QuestionnaireSourceRequestTypesEnum::WEB,
                        );
                    } catch (ModelNotFoundException) {
                        $validator->errors()->add('question_id', 'Question service not found');
                        return;
                    }

                    // Try to reformat to answer as we expect it to be
                    try {
                        $answer    = $service->reformatAnswerFromWeb($this->{$question->slug});
                        $answers[] = [
                            'id'      => $question->id,
                            'slug'    => $question->slug,
                            'answers' => $answer
                        ];
                    } catch (\Throwable $e) {
                        logError($e);
                        $validator->errors()->add($question->slug, 'Unable to process answer data');
                        return;
                    }

                    // need specific validation depending on the service
                    if ($service->mustBeValidated() && !$service->validateOverWeb($answer)) {
                        $validator->errors()->add($question->slug, $service->getValidationErrorMessage());
                    }
                });

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->merge([
                    'client'  => $client,
                    'answers' => $answers,
                ]);
            }
        ];
    }
}

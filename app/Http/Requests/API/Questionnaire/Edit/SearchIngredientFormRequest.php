<?php

namespace App\Http\Requests\API\Questionnaire\Edit;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request responsible for searching ingredients.
 *
 * @property string $search
 * @property \App\Models\User $user
 *
 * @used-by \App\Http\Controllers\API\Questionnaire\QuestionnaireAPIController::searchIngredients()
 * @package App\Http\Requests\Questionnaire
 */
final class SearchIngredientFormRequest extends FormRequest
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
            'search' => ['required', 'string'],
        ];
    }
}

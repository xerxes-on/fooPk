<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Data for adding category to a recipe.
 *
 * @property int $recipe_id
 * @property string $category_name
 *
 * @package App\Http\Requests\API
 */
final class AddCategoryPayload extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'recipe_id'     => ['required', 'integer', 'min:1'],
            'category_name' => ['required', 'string', 'min:1', 'max:256'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge(['category_name' => trim($this->category_name)]);
    }
}

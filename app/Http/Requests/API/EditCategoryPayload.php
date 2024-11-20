<?php

namespace App\Http\Requests\API;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * Data for editing a custom category.
 *
 * @property int $category_id
 * @property string $category_name
 * @property \App\Models\CustomRecipeCategory $category
 *
 * @package App\Http\Requests\API
 */
final class EditCategoryPayload extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'category_id'   => ['required', 'integer', 'min:1'],
            'category_name' => ['required', 'string', 'min:1', 'max:256'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function prepareForValidation()
    {
        try {
            $category = $this->user()->customRecipeCategories()->where('id', $this->category_id)->firstOrFail();
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages(
                ['category_id' => "You don't have custom category with ID $this->category_id."]
            );
        }
        $this->merge(['category' => $category]);
    }
}

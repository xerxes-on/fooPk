<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Requests\Admin;

use App\Enums\Admin\Permission\PermissionEnum;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Form request for creating or editing ingredient tags.
 *
 * @property int|null $id
 * @property string|null $slug
 * @property array $ingredients
 *
 * @package Modules\Ingredient\Http\Requests\Admin
 */
final class IngredientTagStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo(PermissionEnum::CREATE_RECIPE_TAGS->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'   => ['integer', 'nullable'],
            'slug' => [
                is_null($this->id) ? 'required' : 'nullable',
                'unique:Modules\Ingredient\Models\IngredientTag,slug',
                'string',
                'min:3',
                'max:20'
            ],
            'ingredients' => ['nullable', 'array'],
            ...RuleFactory::make(
                [
                    '%title%' => ['required', 'string'],
                ]
            )
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_null($this->slug)) {
            return;
        }

        $this->merge(
            [
                'slug' => Str::slug(sanitize_string($this->slug), '_'),
            ]
        );
    }

}

<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for searching ingredients.
 *
 * @property string|null $search_name
 * @property array|null $tags
 * @property int|null $category_id
 * @property array $filters
 *
 * @package Modules\Ingredient\Http\Requests\Admin
 */
final class SearchIngredientRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search_name' => ['nullable', 'string'],
            'tags.*'      => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    public function passedValidation(): void
    {
        $this->merge(
            [
                'filters' => [
                    'search_name' => $this->search_name,
                    'tags'        => $this->tags,
                    'category_id' => $this->category_id
                ]
            ]
        );
    }
}

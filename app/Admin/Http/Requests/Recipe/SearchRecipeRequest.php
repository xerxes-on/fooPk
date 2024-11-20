<?php

declare(strict_types=1);

namespace App\Admin\Http\Requests\Recipe;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for searching recipes.
 *
 * @property string|null $search_name
 * @property int|null $status
 * @property int|null $translations_done
 * @property int|null $ingestion
 * @property int|null $complexity
 * @property int|null $cost
 * @property int|null $diet
 * @property array|null $ingredients
 * @property array|null $recipe_tags
 * @property array|null $variable_ingredients
 * @property array $filters
 *
 * @package App\Http\Requests\Admin\Recipe
 */
final class SearchRecipeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search_name'            => ['nullable', 'string'],
            'status'                 => ['nullable', 'integer'],
            'ingestion'              => ['nullable', 'integer'],
            'translations_done'      => ['nullable', 'integer'],
            'complexity'             => ['nullable', 'integer'],
            'cost'                   => ['nullable', 'integer'],
            'diet'                   => ['nullable', 'integer'],
            'ingredients.*'          => ['nullable', 'integer'],
            'recipe_tags.*'          => ['nullable', 'integer'],
            'variable_ingredients.*' => ['nullable', 'integer'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    public function passedValidation(): void
    {
        $this->merge([
            'filters' => [
                'search_name'          => $this->search_name,
                'status'               => $this->status,
                'translations_done'    => $this->translations_done,
                'ingestion'            => $this->ingestion,
                'complexity'           => $this->complexity,
                'cost'                 => $this->cost,
                'diet'                 => $this->diet,
                'ingredients'          => $this->ingredients,
                'recipe_tags'          => $this->recipe_tags,
                'variable_ingredients' => $this->variable_ingredients,
            ]
        ]);
    }
}

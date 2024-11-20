<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Parameters for All Recipes.
 *
 * @property-read integer|null $per_page
 * @property-read integer|null $page
 * @property-read string|null $search_name
 * @property-read integer|null $ingestion
 * @property-read integer|null $replacement_ingestion
 * @property-read integer|null $complexity
 * @property-read integer|null $cost
 * @property-read integer|null $diet
 * @property-read boolean|null $invalid
 * @property-read integer|null $seasons
 * @property-read boolean|null $favorite
 * @property-read integer|null $custom_category
 * @property-read boolean|null $excluded
 * @property-read int|null $recipe_tag
 *
 */
final class AllRecipesFilters extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'per_page'              => ['integer', 'min:1', 'max:300'],
            'page'                  => ['integer', 'min:1'],
            'search_name'           => ['string'],
            'ingestion'             => ['integer', 'min:1'],
            'replacement_ingestion' => ['integer', 'min:1'], // used for getting recipes for replacement in a meal
            'complexity'            => ['integer', 'min:1'],
            'cost'                  => ['integer', 'min:1'],
            'diet'                  => ['integer', 'min:1'],
            'invalid'               => ['boolean'],
            'seasons'               => ['integer', 'min:1'],
            'favorite'              => ['boolean'],
            'custom_category'       => ['integer', 'min:1'],
            'excluded'              => ['boolean'],
            'recipe_tag'            => ['integer', 'nullable', 'min:1'],
        ];
    }
}

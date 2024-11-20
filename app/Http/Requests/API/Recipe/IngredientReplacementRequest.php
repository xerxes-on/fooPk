<?php

namespace App\Http\Requests\API\Recipe;

use App\Http\Traits\HandleRecipeReplacementFormRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Data for ingredient replacement.
 *
 * @property-read \Carbon\Carbon $date
 * @property-read \App\Models\Ingestion $ingestion
 * @property-read int $old_ingredient
 * @property-read int $new_ingredient
 * @property-read int|null $amount
 * @property-read int $ingestionIntValue Value of ingestion obtained from enum
 * @package App\Http\Requests\API\Recipe
 */
final class IngredientReplacementRequest extends FormRequest
{
    use HandleRecipeReplacementFormRequest;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date'           => ['required', 'date'],
            'ingestion'      => ['required', 'string'],
            'old_ingredient' => ['required', 'integer', 'min:1'],
            'new_ingredient' => ['required', 'integer', 'min:1'],
            'amount'         => ['nullable', 'integer']
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(
            [
                'old_ingredient' => (int)$this->old_ingredient,
                'new_ingredient' => (int)$this->new_ingredient,
                'amount'         => is_null($this?->amount) ? null : (int)$this->amount
            ]
        );
    }
}

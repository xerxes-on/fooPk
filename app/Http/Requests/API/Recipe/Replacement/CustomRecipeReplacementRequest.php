<?php

namespace App\Http\Requests\API\Recipe\Replacement;

use App\Http\Traits\HandleRecipeReplacementFormRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Data for creation and replacement of custom recipes.
 *
 * @property \Carbon\Carbon $date
 * @property \App\Models\Ingestion $ingestion
 * @property array $ingredients
 * @property-read int $ingestionIntValue Value of ingestion obtained from enum
 *
 * @package App\Http\Requests\API\Recipe\Replacement
 */
final class CustomRecipeReplacementRequest extends FormRequest
{
    use HandleRecipeReplacementFormRequest;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date'                        => ['required', 'date'],
            'ingestion'                   => ['required', 'string'],
            'ingredients'                 => ['required', 'array', 'size:3'],
            'ingredients.*.ingredient_id' => ['required', 'integer', 'min:1'],
        ];
    }
}

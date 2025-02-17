<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Meal;

use App\Http\Requests\BaseRequest;
use App\Http\Traits\HandleRecipeReplacementFormRequest;
use App\Models\Ingestion;
use Carbon\Carbon;

/**
 * Parameters for finding a planned meal ingredients.
 *
 * @property-read Carbon $date
 * @property-read Ingestion $ingestion
 * @property-read int $servings
 * @property-read int $ingestionIntValue Value of ingestion obtained from enum
 *
 * @package App\Http\Requests\API
 */
final class PlannedMealIngredientsRequest extends BaseRequest
{
    use HandleRecipeReplacementFormRequest;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date'      => ['required', 'date'],
            'ingestion' => ['required', 'string'],
            'servings'  => ['required', 'numeric'],
        ];
    }
}

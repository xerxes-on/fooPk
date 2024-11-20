<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Meal;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\HandleRecipeSkipFormRequest;
use App\Models\Ingestion;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Data necessary to skip/unskip a planned meal.
 *
 * @property-read Carbon $date
 * @property-read string $mealTime
 * @property-read int|bool $isEatOut
 * @property-read Ingestion $ingestion
 * @property-read int $recipeType
 *
 * @package App\Http\Requests\API
 */
final class SkipMealRequest extends FormRequest
{
    use HandleRecipeSkipFormRequest;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date'       => ['required', 'date'],
            'mealtime'   => ['required', 'string', 'in:' . implode(',', MealtimeEnum::namesLower())],
            'isEatOut'   => ['required', 'boolean'],
            'recipeType' => ['required', 'integer', 'in:' . implode(',', RecipeTypeEnum::values())],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'isEatOut' => (int)$this->isEatOut,
        ]);
    }
}

<?php

namespace App\Http\Requests\Recipe;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Http\Traits\HandleRecipeSkipFormRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for recipe skipping.
 *
 * @property int $recipe    ID of initial recipe
 * @property string $mealtime
 * @property string|\Carbon\Carbon $date
 * @property bool $isEatOut
 * @property int $recipeType
 * @property-read \App\Models\Ingestion $ingestion
 * @property-read int $ingestionIntValue
 *
 * @package App\Http\Requests
 */
final class SkipRecipeRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;
    use HandleRecipeSkipFormRequest;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe'     => ['required', 'integer'],
            'mealtime'   => ['required', 'string', 'in:' . implode(',', MealtimeEnum::namesLower())],
            'date'       => ['required', 'date'],
            'isEatOut'   => ['required', 'boolean'],
            'recipeType' => ['required', 'integer', 'in:' . implode(',', RecipeTypeEnum::values())],
        ];
    }
}

<?php

namespace App\Http\Requests\Recipe;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Http\Traits\HandleRecipeSkipFormRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for recipe replacement.
 *
 * @property int $recipe    ID of initial recipe
 * @property int $change   ID of recipe to replace with
 * @property string $mealtime
 * @property string|\Carbon\Carbon $date
 * @property int $recipeType
 *
 * @property-read int $ingestionIntValue Value of ingestion obtained from enum
 *
 * @package App\Http\Requests
 */
final class RecipeReplacementRequest extends FormRequest
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
            'change'     => ['required', 'integer'],
            'mealtime'   => ['required', 'string', 'in:' . implode(',', MealtimeEnum::namesLower())],
            'date'       => ['required', 'date'],
            'recipeType' => ['required', 'integer', 'in:' . implode(',', RecipeTypeEnum::values())],
        ];
    }
}

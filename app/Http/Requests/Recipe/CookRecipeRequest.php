<?php

namespace App\Http\Requests\Recipe;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Http\Traits\HandleRecipeSkipFormRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request to mark recipe as cooked (completed).
 *
 * @property int $recipe    ID of initial recipe
 * @property string $mealtime
 * @property string|\Carbon\Carbon $date
 * @property string $rate
 * @property int $recipeType
 * @property-read \App\Models\Ingestion $ingestion
 * @property-read int $ingestionIntValue
 *
 * @package App\Http\Requests
 */
final class CookRecipeRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;
    use HandleRecipeSkipFormRequest;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe'     => ['numeric', 'required'], // recipe id
            'date'       => ['date', 'required'],
            'rate'       => ['numeric', 'nullable'],
            'mealtime'   => ['string', 'required', 'in:' . implode(',', MealtimeEnum::namesLower())],
            'recipeType' => ['numeric', 'required', 'in:' . implode(',', RecipeTypeEnum::values())],
        ];
    }
}

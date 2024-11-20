<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Requests;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request to add recipe to shopping list request.
 *
 * @property-read int $recipe_id
 * @property-read int $recipe_type
 * @property-read string $date
 * @property-read int $mealtime
 * @property-read int $portions
 *
 * @package App\Http\Requests\ShoppingList
 */
final class AddRecipeToShoppingListRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe_id'   => ['required', 'integer'],
            'recipe_type' => ['required', 'integer', 'in:' . implode(',', RecipeTypeEnum::values())],
            'date'        => ['required', 'string', 'date_format:Y-m-d'],
            'mealtime'    => ['required', 'integer', 'in:' . implode(',', MealTimeEnum::values())],
            'portions'    => ['integer', 'min:1', 'max:10'],
        ];
    }
}

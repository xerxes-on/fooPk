<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Requests;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request for changing recipe servings.
 *
 * @property-read int $recipe_id
 * @property-read int $servings
 * @property-read int|null $mealtime Value is represented in MealtimeEnum.php
 * @property-read string|null $meal_day
 * @property-read int $recipe_type Value is represented in RecipeTypeEnum.php
 *
 * @package App\Http\Requests\ShoppingList
 */
final class ChangeRecipeServingsRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe_id'   => ['required', 'integer', 'min:1'],
            'servings'    => ['required', 'integer', 'min:1', 'max:10'],
            'mealtime'    => ['nullable', 'integer', 'in:' . implode(',', MealtimeEnum::values())],
            'recipe_type' => ['integer', 'in:' . implode(',', RecipeTypeEnum::values())],
            'meal_day'    => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->merge(
                    [
                        'recipe_id'   => (int)$this->recipe_id,
                        'servings'    => (int)$this->servings,
                        'mealtime'    => $this->mealtime ? (int)$this->mealtime : null,
                        'recipe_type' => (int)$this->recipe_type,
                    ]
                );
            }
        ];
    }
}

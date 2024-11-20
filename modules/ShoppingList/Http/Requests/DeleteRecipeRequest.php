<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Requests;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\ShoppingList\Models\ShoppingList;

/**
 * DeleteRecipe from shopping list form request.
 *
 * @property-read int $list_id
 * @property-read int $recipe_id
 * @property-read int $recipe_type
 * @property-read string|null $meal_day
 * @property-read int|null $mealtime
 *
 * @package App\Http\Requests\ShoppingList
 */
final class DeleteRecipeRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'list_id'   => ['required', 'integer', 'exists:' . ShoppingList::class . ',id'],
            'recipe_id' => [
                'required',
                'integer',
                Rule::exists('shopping_lists_recipes')->where(fn(Builder $query) => $query->where('list_id', $this->list_id)),
            ],
            'recipe_type' => ['required', 'integer', 'in:' . implode(',', RecipeTypeEnum::values())],
            'meal_day'    => ['nullable', 'date_format:Y-m-d'],
            'mealtime'    => ['nullable', 'integer', 'in:' . implode(',', MealtimeEnum::values())],
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
                        'list_id'     => (int)$this->list_id,
                        'recipe_id'   => (int)$this->recipe_id,
                        'recipe_type' => (int)$this->recipe_type,
                        'mealtime'    => $this->mealtime ? (int)$this->mealtime : null,
                    ]
                );
            }
        ];
    }
}

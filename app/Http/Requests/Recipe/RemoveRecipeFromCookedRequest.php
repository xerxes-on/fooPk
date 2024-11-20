<?php

namespace App\Http\Requests\Recipe;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request to remove recipe from cooked (completed).
 *
 * @property int $recipe
 * @property Carbon $date
 * @property int $recipeType
 *
 * @package App\Http\Requests\Recipe
 */
final class RemoveRecipeFromCookedRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe'     => ['required', 'integer'],
            'date'       => ['required', 'date'],
            'recipeType' => ['numeric', 'required', 'in:' . implode(',', RecipeTypeEnum::values())],
        ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $date = Carbon::parse($this->date);
                } catch (InvalidFormatException $e) {
                    $validator->errors()->add('date', $e->getMessage());
                    return;
                }

                $this->merge([
                    'date' => $date,
                ]);
            }
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Meal;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

final class CalculateIngredientsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe_id'   => ['required', 'numeric'],
            'recipe_type' => ['required', Rule::in(RecipeTypeEnum::values())],
            'servings'    => ['required', 'numeric'],
        ];
    }
}

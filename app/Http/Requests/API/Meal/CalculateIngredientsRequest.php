<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Meal;

use App\Http\Requests\BaseRequest;

final class CalculateIngredientsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe_id'   => ['required', 'numeric'],
            'recipe_type' => ['required', 'string'],
            'servings'    => ['required', 'numeric'],
        ];
    }
}

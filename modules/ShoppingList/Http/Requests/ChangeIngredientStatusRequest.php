<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Requests;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Changing status of ingredient in shopping list form request.
 *
 * @property-read integer $ingredient_id Actual record ID
 * @property-read boolean|int $completed
 *
 * @package App\Http\Requests\ShoppingList
 */
final class ChangeIngredientStatusRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ingredient_id' => ['required', 'integer'],
            'completed'     => ['required', 'boolean']
        ];
    }
}

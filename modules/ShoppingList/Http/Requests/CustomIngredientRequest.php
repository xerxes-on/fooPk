<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Requests;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request to add custom ingredient to shopping list.
 *
 * @property-read string $custom_ingredient
 *
 * @package App\Http\Requests\ShoppingList
 */
final class CustomIngredientRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'custom_ingredient' => ['required', 'string', 'min:3', 'max:191'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Requests;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for deleting an ingredient from a shopping list.
 *
 * @property-read int $ingredient_id
 * @note $ingredient_id is PK of ingredient column
 * @package App\Http\Requests\ShoppingList
 */
final class DeleteIngredientRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ingredient_id' => ['required', 'integer'] // A PK (id) of record, not just ingredient_id column
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\API;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Data needed to detach a category from a recipe.
 *
 * @property int $recipe_id
 * @property int $category_id
 *
 * @package App\Http\Requests\API
 */
final class DetachCategoryPayload extends FormRequest
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
            'recipe_id'   => ['required', 'integer', 'min:1'],
            'category_id' => ['required', 'integer', 'min:1'],
        ];
    }
}

<?php

namespace App\Admin\Http\Requests\Recipe;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for validating recipe distribution data.
 *
 * @property int|null $id
 * @property array $recipes
 * @property string|null $comment
 *
 * @package App\Http\Requests\Admin\Recipe
 */
final class RecipeDistributionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo(PermissionEnum::RECIPE_DISTRIBUTION->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'        => ['integer', 'nullable'],
            'recipes'   => ['required', 'array'],
            'recipes.*' => ['required', 'integer', 'min:1'],
            'comment'   => ['string', 'nullable', 'max:65535'],
        ];
    }
}

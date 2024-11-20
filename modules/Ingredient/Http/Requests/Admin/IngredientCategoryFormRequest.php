<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Requests\Admin;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Class IngredientCategoryFormRequest
 *
 * @property-read string $name
 * @property-read int|null $id
 * @property-read int|null $main_category
 * @property-read int|null $mid_category
 * @property-read bool $jobExists
 * @property-read array<int,int> $diets
 *
 * @package Modules\Ingredient\Http\Requests\Admin
 */
final class IngredientCategoryFormRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:191'],
            'id'            => ['nullable', 'integer'],
            'main_category' => ['nullable', 'integer'],
            'mid_category'  => ['nullable', 'integer'],
            'jobExists'     => ['nullable', 'boolean'],
            'diets'         => ['nullable', 'array'],
            'diets.*'       => ['integer'],
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
                        'name'          => $this->name,
                        'id'            => is_null($this?->id) ? null : (int)$this->id, // Null meaning record is creating
                        'main_category' => is_null($this?->main_category) ? null : (int)$this->main_category,
                        'mid_category'  => is_null($this?->mid_category) ? null : (int)$this->mid_category,
                        'diets'         => is_null($this?->diets) ? [] : $this->diets,
                    ]
                );
            },
        ];
    }
}

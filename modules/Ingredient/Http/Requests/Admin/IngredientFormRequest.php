<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Requests\Admin;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Ingredient Form Request
 *
 * @property-read int|null $id
 * @property-read string $name
 * @property-read int $category_id
 * @property-read int|float $proteins
 * @property-read int|float $fats
 * @property-read int|float $carbohydrates
 * @property-read int|float $calories
 * @property-read int $unit_id
 * @property-read int|null $alternative_unit_id
 * @property-read int|null $unit_amount
 * @property-read array|null $seasons
 * @property-read array|null $tags
 * @property-read array $hint
 * @property-read array $vitamins
 *
 * @package Modules\Ingredient\Http\Requests
 */
final class IngredientFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo(PermissionEnum::CREATE_INGREDIENT->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'                  => ['integer', 'nullable'],
            'name'                => ['required', 'string', 'max:191'],
            'name_plural'         => ['required', 'string', 'max:191'],
            'category_id'         => ['required', 'integer'],
            'proteins'            => ['required', 'numeric'],
            'fats'                => ['required', 'numeric'],
            'carbohydrates'       => ['required', 'numeric'],
            'calories'            => ['required', 'numeric'],
            'unit_id'             => ['required', 'integer'],
            'alternative_unit_id' => ['integer', 'nullable'],
            'unit_amount'         => ['integer', 'nullable'],
            'seasons'             => ['array', 'nullable'],
            'seasons.*'           => ['integer'],
            'tags'                => ['array', 'nullable'],
            'tags.*'              => ['integer'],
            'hint'                => ['array'],
            'hint.content'        => ['string', 'nullable', 'max:65535'],
            'hint.link_text'      => ['string', 'nullable', 'required_with:hint.link_url', 'max:191'],
            'hint.link_url'       => ['url:https', 'nullable', 'required_with:hint.link_text', 'max:191'],
            'vitamins'            => ['array'],
            'vitamins.*'          => ['integer'],
        ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // missing alternative unit
                if ((int)$this->unit_amount > 0 && is_null($this->alternative_unit_id)) {
                    $validator->errors()->add(
                        'alternative_unit_id',
                        trans(
                            'validation.required_with',
                            [
                                'attribute' => trans('ingredient::admin.secondary_unit_title'),
                                'values'    => trans('ingredient::common.unit_amount')
                            ]
                        )
                    );
                    return;
                }

                // missing unit amount
                if ((int)$this->unit_amount === 0 && (int)$this->alternative_unit_id > 0) {
                    $validator->errors()->add(
                        'unit_amount',
                        trans(
                            'validation.required_with',
                            [
                                'attribute' => trans('ingredient::common.unit_amount'),
                                'values'    => trans('ingredient::admin.secondary_unit_title')
                            ]
                        )
                    );
                }
            }
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = data_get($this->validator->validated(), $key, $default);
        if (is_array($data) && array_key_exists('unit_amount', $data) && is_null($data['unit_amount'])) {
            $data['unit_amount'] = 0;
        }
        return $data;
    }
}

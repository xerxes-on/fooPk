<?php

namespace Modules\FlexMeal\Http\Requests;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request to store a flexmeal.
 *
 * @property-read string $meal
 * @property-read string $mealtime
 * @property-read string|null $flexmeal Title of flexmeal
 * @property-read array $ingredients
 * @property-read string|null $notes
 * @property-read \Illuminate\Http\UploadedFile|null $image
 * @property-read int $user_id
 *
 * @package App\Http\Requests\Flexmeal
 */
final class StoreFlexmealRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'meal'                        => ['required', 'string', 'max:20'],
            'flexmeal'                    => ['string', 'nullable', 'max:191'], // Title
            'ingredients'                 => ['required', 'array', 'min:1'],
            'ingredients.*.amount'        => ['required', 'numeric', 'min:0'],
            'ingredients.*.ingredient_id' => ['required', 'integer', 'min:1'],
            'notes'                       => ['string', 'nullable', 'max:65530'],
            'image'                       => ['image', 'nullable', 'max:10240', 'mimes:jpg,jpeg,png'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'ingredients.required' => trans('common.please_add_ingredient'),
            'ingredients.min'      => trans('common.please_add_ingredient')
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
                        'user_id'  => $this->user()->id,
                        'mealtime' => $this->meal,
                        'name'     => empty($this->flexmeal) ? now()->format('d.m.Y') : strip_tags(trim($this->flexmeal)),
                        'notes'    => empty($this->notes) ? null : strip_tags(trim($this->notes)),
                        'image'    => $this->file('image')
                    ]
                );
            }
        ];
    }
}

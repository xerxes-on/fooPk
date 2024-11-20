<?php

namespace Modules\FlexMeal\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Data for updating image of a FlexMeal.
 *
 * @property-read \Illuminate\Http\UploadedFile $new_image
 *
 * @package App\Http\Requests\API\Flexmeal
 */
final class FlexMealUpdateImageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'new_image' => ['image', 'max:10240', 'mimes:jpg,jpeg,png'],
        ];
    }
}

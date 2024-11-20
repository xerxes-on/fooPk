<?php

namespace App\Admin\Http\Requests;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AllergyFormRequest
 *
 * @package App\Http\Requests
 */
final class AllergyFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo(PermissionEnum::CREATE_DISEASES->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'    => ['required'],
            'slug'    => ['required'],
            'type_id' => ['required', 'exists:allergy_types,id']
        ];
    }
}

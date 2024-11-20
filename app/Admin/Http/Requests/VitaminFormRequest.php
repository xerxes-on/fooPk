<?php

namespace App\Admin\Http\Requests;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class VitaminFormRequest
 *
 * @property int|null $id
 * @property string $name
 *
 * @package App\Http\Requests
 */
final class VitaminFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo(PermissionEnum::CREATE_VITAMINS->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'   => ['integer', 'nullable'],
            'name' => ['required', 'string']
        ];
    }
}

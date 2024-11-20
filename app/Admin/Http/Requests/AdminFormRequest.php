<?php

namespace App\Admin\Http\Requests;

use App\Enums\Admin\Permission\{PermissionEnum};
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AdminFormRequest
 *
 * @property int|null $id
 * @property int|null $status
 * @property string $name
 * @property string $email
 * @property string|null $new_password
 * @property int|string $role
 * @property array|null $liableClients
 *
 * @package App\Http\Requests\Admin
 */
final class AdminFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo(PermissionEnum::CREATE_ADMIN->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $creatingNewAdmin = is_null($this->id) ? 'required' : 'nullable';
        return [
            'id'              => ['nullable', 'integer'],
            'status'          => ['nullable', 'integer'], // Add this line to the 'rules' method
            'name'            => ['required', 'string'],
            'email'           => ['required', 'email', 'unique:admins,email,' . $this->id],
            'new_password'    => [$creatingNewAdmin, 'string', 'min:8'],
            'role'            => ['required', 'integer'],
            'liableClients'   => ['nullable', 'array'],
            'liableClients.*' => ['integer'],
        ];
    }
}

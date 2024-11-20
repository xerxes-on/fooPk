<?php

namespace App\Admin\Http\Requests\Client;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for creating a new client.
 *
 * @property string $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $new_password
 * @property bool|null $status
 * @property bool|null $mark_tested
 * @property bool|null $automatic_meal_generation
 *
 * @uses \App\Admin\Http\Controllers\ClientsAdminController::create()
 * @package App\Http\Requests
 */
final class ClientCreateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo(PermissionEnum::CREATE_CLIENT->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status'                    => ['boolean', 'nullable'],
            'mark_tested'               => ['boolean', 'nullable'],
            'automatic_meal_generation' => ['boolean', 'nullable'],
            'first_name'                => ['string', 'required', 'max:191'],
            'last_name'                 => ['string', 'nullable', 'sometimes', 'max:191'],
            'email'                     => ['required', 'unique:App\Models\User,email'],
            'new_password'              => ['string', 'nullable'], // TODO: should we allow special validation here?
        ];
    }
}

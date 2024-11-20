<?php

namespace App\Admin\Http\Requests\Client;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ClientFormRequest
 *
 * @property int $client_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $new_password
 * @property string $lang
 * @property int $status
 * @property int $mark_tested
 * @property int $allow_marketing
 * @property string $admin_note
 *
 * @uses \App\Admin\Http\Controllers\ClientsAdminController::store()
 * @package App\Http\Requests
 */
final class ClientFormRequest extends FormRequest
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
            'client_id'       => ['integer', 'nullable'],
            'first_name'      => ['string', 'required', 'max:191'],
            'last_name'       => ['string', 'nullable', 'sometimes', 'max:191'],
            'email'           => ['required', 'unique:users,email,' . $this->id],
            'new_password'    => ['string', 'nullable'], // TODO: should we allow special validation here?
            'lang'            => ['string', 'in:en,de'],
            'status'          => ['boolean'],
            'mark_tested'     => ['boolean'],
            'allow_marketing' => ['boolean']
        ];
    }
}

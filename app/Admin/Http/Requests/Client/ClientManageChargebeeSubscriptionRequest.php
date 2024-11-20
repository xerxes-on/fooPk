<?php

namespace App\Admin\Http\Requests\Client;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * TODO:: move to chargebee module
 * Class ClientFormRequest
 *
 * @property string $chargebee_subscription_id
 * @property int $client_id
 *
 * @package App\Http\Requests
 */
final class ClientManageChargebeeSubscriptionRequest extends FormRequest
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
            'chargebee_subscription_id' => ['required', 'string', 'max:190'],
            'client_id'                 => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'chargebee_subscription_id.exists' => trans('common.chargebee_subscription_id_invalid_or_not_exists'),
        ];
    }
}

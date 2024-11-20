<?php

namespace App\Admin\Http\Requests\Client;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for toggling client questionnaire edit status.
 *
 * @property int $clientId
 * @property int|string $is_editable
 *
 * @package App\Http\Requests
 */
final class ClientFormToggleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo(PermissionEnum::MANAGE_CLIENT_FORMULAR->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'clientId'    => ['required', 'exists:users,id'],
            'is_editable' => ['required', 'string', 'in:true,false'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $this->merge(['is_editable' => $this->is_editable === 'true' ? 1 : 0]);
    }
}

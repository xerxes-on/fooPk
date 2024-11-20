<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Data necessary to change users password.
 *
 * @property string $old_password
 * @property string $new_password
 *
 * @package App\Http\Requests\API
 */
final class ChangePasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'old_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', Password::min(8)->uncompromised()]
        ];
    }
}

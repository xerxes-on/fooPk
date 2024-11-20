<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ForgotPasswordPayload
 *
 * @property string $email
 *
 * @package App\Http\Requests
 */
final class ForgotPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}

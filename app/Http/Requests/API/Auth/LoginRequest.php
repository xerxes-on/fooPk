<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Ensures valid user credentials are passed when logging in.
 *
 * @property string $email
 * @property string $password
 *
 * @package App\Http\Requests\API\Auth
 */
final class LoginRequest extends BaseRequest
{
    /**
     * Describe how to validate the data.
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email', 'min:5'],
            'password' => ['required'],
        ];
    }
}

<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Form request to register new temporarily user.
 *
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $lang
 *
 * @package App\Http\Requests\API\Auth
 */
final class RegisterUserRequest extends BaseRequest
{
    /**
     * Describe how to validate the data.
     */
    public function rules(): array
    {
        return [
            'first_name' => ['string', 'required', 'min:2'],
            'email'      => ['required', 'email:rfc,dns', 'unique:App\Models\User,email', 'min:5'],
            'lang'       => ['string', 'required', 'in:de,en'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['last_name' => '']);
    }
}

<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\BaseRequest;
use App\Models\User;

/**
 * Form request to finalize new register new user.
 *
 * @property string $email
 * @property string $password
 * @property User $user
 * @property string $fingerprint
 *
 * @package App\Http\Requests\API\Auth
 */
final class FinishUserRegistrationRequest extends BaseRequest
{
    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge(['user' => $this->user() ?? User::whereEmail($this->email)->first()]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (is_null($this->user)) {
            return false;
        }

        if (!$this->user->hasVerifiedEmail()) {
            return false;
        }

        return true;
    }

    /**
     * Describe how to validate the data.
     * @note Due to mobile implementation specifics, validation is performed in the controller.
     */
    public function rules(): array
    {
        return [];
    }
}

<?php

namespace App\Http\Requests\API\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request to verify user email over API.
 *
 * User is not authenticated here and doest exist in request instance.
 *
 * @property User $user
 * @property string|int|null $id
 * @property string|null $hash
 *
 * @package App\Http\Requests\API\Auth
 */
final class EmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (is_null($this->user)) {
            return false;
        }

        if (!hash_equals((string)$this->user->getKey(), (string)$this->id)) {
            return false;
        }

        if (!hash_equals(sha1($this->user->getEmailForVerification()), (string)$this->hash)) {
            return false;
        }

        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge(['user' => $this->user() ?? User::whereId($this->id)->first()]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'   => ['required', 'integer'],
            'hash' => ['required', 'string'],
        ];
    }
}

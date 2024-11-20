<?php

namespace App\Http\Requests\API\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Data necessary to handle email confirmation for user.
 *
 * @property string $email
 * @property User $user
 *
 * @package App\Http\Requests\API\Auth
 */
final class EmailConfirmationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc,dns']
        ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $user = User::whereEmail($this->email)->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    $validator->errors()->add('email', 'No such email');
                    return;
                }
                $this->merge(['user' => $user]);
            }
        ];
    }
}

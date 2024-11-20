<?php

namespace App\Http\Requests\API\Profile;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Delete user form request
 *
 * @property string $timestamp
 * @property string|null $reason
 *
 * @package App\Http\Requests\API\Profile
 */
final class DeleteUserRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'timestamp' => ['required', 'date_format:Y-m-d H:i:s'],
            'reason'    => ['nullable', 'string', 'min:2']
        ];
    }
}

<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\User;

/**
 * Form request to verify user email over Web app.
 *
 * @property User $user
 *
 * @package App\Http\Requests\Auth
 */
final class EmailVerificationRequest extends BaseRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge(['user' => $this->user() ?? User::whereId($this->route('id'))->first()]);
    }
}

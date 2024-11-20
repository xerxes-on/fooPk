<?php

namespace App\Http\Traits;

use App\Enums\Admin\Permission\RoleEnum;

trait CanAuthorizeAdminRequests
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasRole(RoleEnum::ADMIN->value);
    }
}

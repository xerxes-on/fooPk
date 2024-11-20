<?php

namespace App\Http\Traits;

use App\Enums\Admin\Permission\RoleEnum;
use App\Models\Admin;

trait HandleAdminDeletePolicy
{
    final public function delete(Admin $admin): bool
    {
        return $admin->hasRole(RoleEnum::ADMIN->value);
    }
}

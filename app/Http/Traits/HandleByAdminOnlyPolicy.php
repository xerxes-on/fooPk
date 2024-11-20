<?php

namespace App\Http\Traits;

use App\Enums\Admin\Permission\RoleEnum;
use App\Models\Admin;

trait HandleByAdminOnlyPolicy
{
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasRole(RoleEnum::ADMIN->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasRole(RoleEnum::ADMIN->value);
    }

    public function edit(Admin $admin): bool
    {
        return $admin->hasRole(RoleEnum::ADMIN->value);
    }
}

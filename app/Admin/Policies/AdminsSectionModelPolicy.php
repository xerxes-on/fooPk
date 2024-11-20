<?php

namespace App\Admin\Policies;

use App\Enums\Admin\Permission\{PermissionEnum, RoleEnum};
use App\Http\Traits\HandleAdminBeforePolicy;
use App\Http\Traits\HandleAdminDeletePolicy;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

final class AdminsSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_ALL_ADMINS->value);
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

<?php

declare(strict_types=1);

namespace App\Admin\Policies;

use App\Admin\Http\Section\User\Users;
use App\Enums\Admin\Permission\{PermissionEnum, RoleEnum};
use App\Http\Traits\HandleAdminBeforePolicy;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class UsersSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_ALL_CLIENTS->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_CLIENT->value);
    }

    public function edit(Admin $admin, Users $section, User $model): bool
    {
        if ($admin->hasRole(RoleEnum::CONSULTANT->value)) {
            return $admin->isResponsibleForClient($model->id);
        }
        return $admin->hasPermissionTo(PermissionEnum::CREATE_CLIENT->value);
    }

    public function delete(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::DELETE_CLIENT->value);
    }
}

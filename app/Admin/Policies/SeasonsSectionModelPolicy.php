<?php

declare(strict_types=1);

namespace App\Admin\Policies;

use App\Enums\Admin\Permission\PermissionEnum;
use App\Enums\Admin\Permission\RoleEnum;
use App\Http\Traits\HandleAdminBeforePolicy;
use App\Http\Traits\HandleAdminDeletePolicy;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

final class SeasonsSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_INGREDIENT_DIETS->value);
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

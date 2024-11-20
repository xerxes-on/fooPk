<?php

declare(strict_types=1);

namespace App\Admin\Policies;

use App\Enums\Admin\Permission\PermissionEnum;
use App\Http\Traits\{HandleAdminBeforePolicy, HandleAdminDeletePolicy};
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

final class RecipeSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_ALL_RECIPES->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_RECIPE->value);
    }

    public function edit(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_RECIPE->value);
    }
}

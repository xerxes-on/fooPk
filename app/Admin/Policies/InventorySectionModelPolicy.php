<?php

declare(strict_types=1);

namespace App\Admin\Policies;

use App\Enums\Admin\Permission\PermissionEnum;
use App\Http\Traits\{HandleAdminBeforePolicy, HandleAdminDeletePolicy};
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

final class InventorySectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_ALL_INVENTORY->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_INVENTORY->value);
    }

    public function edit(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_INVENTORY->value);
    }
}

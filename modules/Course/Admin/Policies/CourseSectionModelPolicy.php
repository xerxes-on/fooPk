<?php

declare(strict_types=1);

namespace Modules\Course\Admin\Policies;

use App\Enums\Admin\Permission\{PermissionEnum};
use App\Http\Traits\HandleAdminBeforePolicy;
use App\Http\Traits\HandleAdminDeletePolicy;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CourseSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_ALL_CHALLENGES->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_CHALLENGES->value);
    }

    public function edit(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_CHALLENGES->value);
    }
}

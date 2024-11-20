<?php

declare(strict_types=1);

namespace Modules\PushNotification\Policies;

use App\Enums\Admin\Permission\PermissionEnum;
use App\Http\Traits\{HandleAdminBeforePolicy, HandleAdminDeletePolicy};
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

final class NotificationSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_ALL_NOTIFICATIONS->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_NOTIFICATIONS->value);
    }

    public function edit(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_NOTIFICATIONS->value);
    }
}

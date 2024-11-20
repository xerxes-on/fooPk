<?php

declare(strict_types=1);

namespace Modules\PushNotification\Policies;

use App\Enums\Admin\Permission\PermissionEnum;
use App\Http\Traits\{HandleAdminBeforePolicy, HandleAdminDeletePolicy};
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

final class NotificationTypeSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_ALL_NOTIFICATION_TYPES->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_NOTIFICATION_TYPES->value);
    }

    public function edit(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_NOTIFICATION_TYPES->value);
    }
}

<?php

namespace App\Http\Traits;

use App\Enums\Admin\Permission\RoleEnum;
use App\Models\Admin;

trait HandleAdminBeforePolicy
{
    public function before(Admin $admin)
    {
        if ($admin->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }
    }
}

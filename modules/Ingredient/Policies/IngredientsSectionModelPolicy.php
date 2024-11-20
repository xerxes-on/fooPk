<?php

declare(strict_types=1);

namespace Modules\Ingredient\Policies;

use App\Enums\Admin\Permission\{PermissionEnum};
use App\Http\Traits\HandleAdminBeforePolicy;
use App\Http\Traits\HandleAdminDeletePolicy;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

final class IngredientsSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_ALL_INGREDIENTS->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_INGREDIENT->value);
    }

    public function edit(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_RECIPE->value);
    }
}

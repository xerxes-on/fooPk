<?php

namespace Modules\Ingredient\Policies;

use App\Enums\Admin\Permission\PermissionEnum;
use App\Http\Traits\{HandleAdminBeforePolicy, HandleAdminDeletePolicy};
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

final class IngredientTagSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleAdminDeletePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::SEE_INGREDIENT_TAGS->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_INGREDIENT_TAGS->value);
    }

    public function edit(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::CREATE_INGREDIENT_TAGS->value);
    }
}

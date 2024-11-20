<?php

declare(strict_types=1);

namespace App\Admin\Policies;

use App\Admin\Http\Section\Recipe\RecipeDistribution as RecipeDistributionSection;
use App\Enums\Admin\Permission\PermissionEnum;
use App\Http\Traits\HandleAdminBeforePolicy;
use App\Models\Admin;
use App\Models\RecipeDistribution;
use Illuminate\Auth\Access\HandlesAuthorization;

final class RecipeDistributionSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;

    public function display(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::RECIPE_DISTRIBUTION->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::RECIPE_DISTRIBUTION->value);
    }

    public function edit(Admin $admin, RecipeDistributionSection $section, RecipeDistribution $model): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::RECIPE_DISTRIBUTION->value) && !$model->is_distributed;
    }

    public function delete(Admin $admin, RecipeDistributionSection $section, RecipeDistribution $model): bool
    {
        return $admin->hasPermissionTo(PermissionEnum::RECIPE_DISTRIBUTION->value) && !$model->is_distributed;
    }
}

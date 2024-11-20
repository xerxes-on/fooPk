<?php

declare(strict_types=1);

namespace App\Admin\Policies;

use App\Http\Traits\{HandleAdminBeforePolicy, HandleByAdminOnlyPolicy};
use Illuminate\Auth\Access\HandlesAuthorization;

final class RecipeComplexitySectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleByAdminOnlyPolicy;
}

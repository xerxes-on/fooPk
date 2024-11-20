<?php

declare(strict_types=1);

namespace Modules\Ingredient\Policies;

use App\Http\Traits\{HandleAdminBeforePolicy, HandleByAdminOnlyPolicy};
use Illuminate\Auth\Access\HandlesAuthorization;

final class IngredientUnitSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleByAdminOnlyPolicy;
}

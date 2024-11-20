<?php

declare(strict_types=1);

namespace App\Admin\Policies;

use App\Http\Traits\{HandleAdminBeforePolicy, HandleByAdminOnlyPolicy};
use Illuminate\Auth\Access\HandlesAuthorization;

final class PageSectionModelPolicy
{
    use HandlesAuthorization;
    use HandleAdminBeforePolicy;
    use HandleByAdminOnlyPolicy;
}

<?php

declare(strict_types=1);

namespace Modules\Course\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

final class UserObtainedCourseEvent
{
    use Dispatchable;

    public function __construct(public readonly User $user)
    {
    }
}

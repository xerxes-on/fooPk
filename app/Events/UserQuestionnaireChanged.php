<?php

declare(strict_types=1);

namespace App\Events;

use App\Listeners\EventBase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserQuestionnaireChanged extends EventBase
{
    use Dispatchable;
    use SerializesModels;
}

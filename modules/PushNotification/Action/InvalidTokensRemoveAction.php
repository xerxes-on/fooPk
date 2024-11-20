<?php

declare(strict_types=1);

namespace Modules\PushNotification\Action;

use Illuminate\Support\Arr;
use Modules\PushNotification\Models\UserDevice;

final class InvalidTokensRemoveAction
{
    public function handle(array $tokens): void
    {
        UserDevice::whereIn('token', Arr::flatten($tokens))->delete();
    }
}

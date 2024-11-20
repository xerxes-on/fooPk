<?php

declare(strict_types=1);

namespace Modules\PushNotification\Exceptions;

use Exception;

/**
 * Thrown in case no users match select criteria during course selection.
 */
final class NoUsersForSelectedCriteria extends Exception
{
}

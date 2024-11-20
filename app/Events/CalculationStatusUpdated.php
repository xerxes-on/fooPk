<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class CalculationStatusUpdated
 *
 * @package App\Events
 */
final class CalculationStatusUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * CalculationStatusUpdated constructor.
     *
     * @param $message
     */
    public function __construct(public $message)
    {
    }
}

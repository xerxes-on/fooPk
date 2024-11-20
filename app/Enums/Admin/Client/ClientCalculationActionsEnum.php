<?php

namespace App\Enums\Admin\Client;

use App\Http\Traits\EnumToArray;

/**
 * Enum for client calculation actions.
 * Used to map actions from request to service methods.
 *
 * @package App\Enums\Admin\Client
 */
enum ClientCalculationActionsEnum: string
{
    use EnumToArray;

    case RESET = 'resetCalculation';

    case RECALCULATE = 'recalculate';

    case STORE_CUSTOM_NUTRIENTS = 'saveCustomNutrients';
}

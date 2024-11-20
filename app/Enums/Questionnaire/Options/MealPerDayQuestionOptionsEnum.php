<?php

declare(strict_types=1);

namespace App\Enums\Questionnaire\Options;

use App\Http\Traits\EnumToArray;

/**
 * Enum determining types of meals per day for user
 *
 * @package App\Enums\Questionnaire
 */
enum MealPerDayQuestionOptionsEnum: string
{
    use EnumToArray;

    case STANDARD         = 'full_3';
    case BREAKFAST_LUNCH  = 'breakfast_lunch';
    case BREAKFAST_DINNER = 'breakfast_dinner';
    case LUNCH_DINNER     = 'lunch_dinner';
}

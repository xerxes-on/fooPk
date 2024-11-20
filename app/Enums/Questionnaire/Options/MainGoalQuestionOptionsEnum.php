<?php

declare(strict_types=1);

namespace App\Enums\Questionnaire\Options;

use App\Http\Traits\EnumToArray;

/**
 * Enum determining Questionnaire Question Main goal options
 *
 * @package App\Enums\Questionnaire
 */
enum MainGoalQuestionOptionsEnum: string
{
    use EnumToArray;

    case LOSE_WEIGHT     = 'lose_weight';
    case HEALTHY_WEIGHT  = 'healthy_weight';
    case IMPROVE_FITNESS = 'improve_fitness';
    case IMPROVE_HEALTH  = 'improve_health';
    case GAIN_WEIGHT     = 'gain_weight';
    case BUILD_MUSCLE    = 'build_muscle';

    public static function getWeightDiffOptions(): array
    {
        return [
            self::LOSE_WEIGHT->value,
            self::GAIN_WEIGHT->value,
        ];
    }
}

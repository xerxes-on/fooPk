<?php

declare(strict_types=1);

namespace App\Enums\Questionnaire;

/**
 * Enum determining Questionnaire Question IDS.
 *
 * @note not the best thing, but some answers are heavily relates on it.
 *
 * @package App\Enums\Questionnaire
 */
enum QuestionnaireQuestionIDEnum: int
{
    case MAIN_GOAL           = 1;
    case WEIGHT_GOAL         = 2;
    case EXTRA_GOAL          = 3;
    case FIRST_NAME          = 4;
    case INFO_WELCOME        = 5;
    case INFO_SALES          = 6;
    case MAIN_GOAL_REASON    = 7;
    case CIRCUMSTANCES       = 8;
    case SOCIABILITY         = 9;
    case INFO_SUPPORT        = 10;
    case DIFFICULTIES        = 11;
    case LIFESTYLE           = 12;
    case DIETS               = 13;
    case MEALS_PER_DAY       = 14;
    case ALLERGIES           = 15;
    case EXCLUDE_INGREDIENTS = 16;
    case INFO_SECURITY       = 17;
    case EMAIL               = 18;
    case INFO_TESTIMONIALS   = 19;
    case SPORTS              = 20;
    case RECIPE_PREFERENCES  = 21;
    case DISEASES            = 22;
    case MOTIVATION          = 23;
    case INFO_MOTIVATION     = 24;
    case INFO_TEAM_DETAILS   = 25;
    case GENDER              = 26;
    case BIRTHDATE           = 27;
    case HEIGHT              = 28;
    case WEIGHT              = 29;
    case FAT_CONTENT         = 30;
    case FEATURES            = 31;
    case INFO_FEATURES       = 32;
    case INFO_BENEFITS       = 33;

    public static function userDietAndDiseases(): array
    {
        return [
            self::DIETS->value,
            self::ALLERGIES->value,
            self::DISEASES->value,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Enums\Questionnaire;

/**
 * Enum determining Questionnaire Question slugs.
 *
 * @package App\Enums\Questionnaire
 */
enum QuestionnaireQuestionSlugsEnum: string
{
    public const MAIN_GOAL           = 'main_goal';
    public const WEIGHT_GOAL         = 'weight_goal';
    public const EXTRA_GOAL          = 'extra_goal';
    public const FIRST_NAME          = 'first_name';
    public const INFO_WELCOME        = 'info_welcome';
    public const INFO_SALES          = 'info_sales';
    public const MAIN_GOAL_REASON    = 'main_goal_reason';
    public const CIRCUMSTANCES       = 'circumstances';
    public const SOCIABILITY         = 'sociability';
    public const INFO_SUPPORT        = 'info_support';
    public const DIFFICULTIES        = 'difficulties';
    public const LIFESTYLE           = 'lifestyle';
    public const DIETS               = 'diets';
    public const MEALS_PER_DAY       = 'meals_per_day';
    public const ALLERGIES           = 'allergies';
    public const EXCLUDE_INGREDIENTS = 'exclude_ingredients';
    public const INFO_SECURITY       = 'info_security';
    public const EMAIL               = 'email';
    public const INFO_TESTIMONIALS   = 'info_testimonials';
    public const SPORTS              = 'sports';
    public const RECIPE_PREFERENCES  = 'recipe_preferences';
    public const DISEASES            = 'diseases';
    public const MOTIVATION          = 'motivation';
    public const INFO_MOTIVATION     = 'info_motivation';
    public const INFO_TEAM_DETAILS   = 'info_team_details';
    public const GENDER              = 'gender';
    public const BIRTHDATE           = 'birthdate';
    public const HEIGHT              = 'height';
    public const WEIGHT              = 'weight';
    public const FAT_CONTENT         = 'fat_content';
    public const FEATURES            = 'features';
    public const INFO_FEATURES       = 'info_features';
    public const INFO_BENEFITS       = 'info_benefits';
}

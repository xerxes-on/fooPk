<?php

namespace App\Enums;

/**
 * Enum determining available mealtime types.
 *
 * @package App\Enums
 */
enum DatabaseTableEnum: string
{
    public const USERS                   = 'users';
    public const QUESTIONNAIRE           = 'questionnaires';
    public const QUESTIONNAIRE_ANSWERS   = 'questionnaire_answers';
    public const CHARGEBEE_SUBSCRIPTIONS = 'chargebee_subscriptions';
    public const USER_RECIPE_CALCULATED  = 'user_recipe_calculated';
    public const RECIPES_TO_USERS        = 'recipes_to_users';
    public const CUSTOM_RECIPES          = 'custom_recipes';
    public const RECIPES                 = 'recipes';
    public const INGESTIONS              = 'ingestions';


}

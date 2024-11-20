<?php

/** @deprecated */

return [


    /*
    |--------------------------------------------------------------------------
    | Formular settings
    |--------------------------------------------------------------------------
    |
    | client can edit own formular at any time by buying this ability.
    */
    'formular_editing_price_foodpoints'                 => env('FORMULAR_EDITING_PRICE_FOODPOINTS', 100),
    'ability_forced_formular_editing_by_client_enabled' => env('ABILITY_FORCED_FORMULAR_EDITING_BY_CLIENT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Formular redirect link
    |--------------------------------------------------------------------------
    |
    | Redirect to this link if Auth:check is not passed
    |
    */
    'redirect_link' => 'https://foodpunk.com/',

    /*
    |--------------------------------------------------------------------------
    | Formular expiration period
    |--------------------------------------------------------------------------
    |
    | Period till which admin will get email notification of formular is not approved automatically
    |
    */
    'expiration_period_in_days' => 10,

    /*
    |--------------------------------------------------------------------------
    | New users bonus
    |--------------------------------------------------------------------------
    |
    | New user default foodpoints bonus amount.
    |
    */
    'new_user_foodpoints_bonus' => 150,

    /*
    |--------------------------------------------------------------------------
    | Formular questions key_code map
    |--------------------------------------------------------------------------
    |
    | All questions are hardcoded to SQL are there is no way co control it.
    | It is hard to perform validation relying on id. This map helps to define the name of validation
    |
    */
    'keycode_map' => [
        1  => 'date_start',
        2  => 'main_target',
        3  => 'growth',
        4  => 'age',
        5  => 'weight',
        6  => 'gender',
        7  => 'fat_percentage',
        8  => 'life_activity',
        9  => 'intensive_sports',
        10 => 'moderate_sports',
        11 => 'light_sports',
        12 => 'carbohydrate_diet',
        13 => 'how_many_carbs',
        14 => 'particularly_important',
        15 => 'disease',
        16 => 'allergy',
        17 => 'any_comments',
        18 => 'daily_routine',
    ],
    // recipes tag selection, answers are duplications from database, uses for formular first time recipe distribution logic  app/Helpers/Calculation.php::recipeDistributionFirstTime
    'recipes_tag_based_on_answers' => [
        'daily_routine' => [
            // WEB-92, https://foodpunk.atlassian.net/wiki/spaces/APP/pages/2228158497/Automation+and+questionnaire+requirements#Asking-the-customer-for-specific-requirements-in-their-daily-routine
            "quick_meals"    => 3,
            "meal_preps"     => 5,
            "cost_effective" => 2,
            "family"         => 4,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Formular Free Editing Period
    |--------------------------------------------------------------------------
    |
    | These period determines when user can edit formular for free.
    |
    |
    */
    'period_of_free_editing_in_days'      => 28,
    'period_of_immediate_edit_in_minutes' => 10,

    /*
    |--------------------------------------------------------------------------
    | Formular various notifications
    |--------------------------------------------------------------------------
    |
    | Various configurations for formular notifications can be set in here.
    | E.g. when displaying alert to user or else.
    |
    */
    'alert_dismiss_period' => 259200,
    // 3 days in seconds
];

<?php

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
    'redirect_link'         => 'https://foodpunk.com/',
    'wp_form_redirect_link' => 'https://foodpunk.com/de/ernaehrungsplan-erstellen/',

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
    | Recipes tag selection
    |--------------------------------------------------------------------------
    |
    | Recipes tag selection, answers are duplications from database, uses for formular first time recipe distribution logic
    | used in app/Helpers/Calculation.php::recipeDistributionFirstTime
    |
    */
    'recipes_tag_based_on_answers' => [
        // recipe preferences from questionnaire to recipes tags
        // WEB-92, https://foodpunk.atlassian.net/wiki/spaces/APP/pages/2228158497/Automation+and+questionnaire+requirements#Asking-the-customer-for-specific-requirements-in-their-daily-routine
        'recipe_preferences' => [
            'quick_meals'     => 3, //fast_recipes
            'meal_prep'       => 5, //meal_prep_recipes
            'cost_effective'  => 2, //low_budget_recipes
            'family_friendly' => 4, //family_friendly_reci
            //'any'             => 1, //first_meal_plan
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

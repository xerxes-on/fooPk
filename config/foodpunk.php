<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Project configuration.
|--------------------------------------------------------------------------
|
| Various hardcoded values specific to Foodpunk project.
|
*/
return [
    'api_max_login_attempts'                => 4,
    'days_recipe_is_new'                    => 7, // TODO: the option should be moved to recipe or calculations config later
    'disable_ingredients_category_deletion' => env('DELETE_INGREDIENTS_CATEGORY', true),

    'users' => [
        /**
         * MIN AMOUNT OF USER RECIPES
         */
        'min_amount_of_user_recipes' => '30',
        /**
         * Interval of sending mail notifications for admins in days
         */
        'interval_for_email_sending' => '7',
    ],

    'new_recipe_price'          => 10,
    'check_user_recalculations' => env('CHECK_USER_RECALCULATIONS', true),
    'show_recipes_images'       => env('SHOW_RECIPES_IMAGES', true),
];

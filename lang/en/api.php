<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during API responses for various
    | messages that we need to display to the user.
    |
    */

    // Auth
    'auth' => [
        'errors' => [
            'email_not_verified'     => 'Your email is not verified.',
            'email_already_verified' => 'Your email has already been verified.',
            'incorrect_creds'        => 'The provided credentials are incorrect.',
        ],
        'success' => [
            'email_verified'          => 'Your email is verified.',
            'logout_success'          => 'You\'re logged out.',
            'login_success'           => 'The sign in is successful.',
            'email_verification_sent' => 'Email verification was sent to your email.',
        ],
        'registration' => [
            'success' => [
                'user_created' => 'New user created successfully.',
            ],
        ],
    ],
    'incorrect_creds' => 'The provided credentials are incorrect.',
    'logout_success'  => 'You\'re logged out.',
    'login_success'   => 'The sign in is successful.',

    // Chargebee
    'chargebee_sync_success' => 'Chargebee sync successfully.',

    // Flexmeal
    'flexmeal_404'           => 'The FlexMeal you are trying to access does not exist',
    'flexmeal_updated'       => 'List updated',
    'flexmeal_image_updated' => 'List image updated',

    // Formular
    'formular_edit_prohibited'   => 'You cannot edit formular',
    'withdraw_fail'              => 'Unable to withdraw foodpoints from balance. Try again later',
    'amount_foodpoints_withdraw' => ':amount foodpoints were withdrawn from your balance',
    'formular_404'               => 'No answers available found',
    'formular'                   => [
        'edit_check_free' => [
            'title'  => 'Do you want to edit your data?',
            'body'   => 'Your meal plan will then be adjusted to your new data in your questionnaire within one business day.',
            'button' => 'Edit data',
        ],
        'edit_check_paid' => [
            'button' => 'Buy',
        ]
    ],

    // Ingredients
    'ingredients_no_formular'      => 'You have to answer survey questions to detect which ingredients are suitable for you.',
    'ingredient_type_error'        => 'Unsupported ingredient type.',
    'ingredient_replacement_error' => 'Replacement of the ingredient is not allowed.',

    // Recipes
    'meal_skipped'                          => 'Meal skipped',
    'exclude_meal_public_error'             => 'Unable to exclude this recipe from meal plan. Try again later or contact our support team.',
    'remove_from_excluded_public_error'     => 'Requested recipe is not marked as excluded. Nothing to restore.',
    'exclude_success_response'              => 'Recipe is excluded from your meal plan and replaced with another.',
    'remove_from_excluded_success_response' => 'Recipe is removed from excluded.',
    'recipe_type_error'                     => 'Current recipe is not custom.',

    // Challenges
    'view_more'     => 'View more',
    'buy_challenge' => 'Buy course',
    'cancel'        => 'Cancel',

    // Posts
    'no_posts' => 'No lesson available',
    'success'=> 'Success',
    'failed'=> 'Failed',
];

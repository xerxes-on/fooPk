<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Helpers Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain helper texts for admin panel.
    |
    */

    // Notification Type
    'notification_icon'      => 'Upload/Select icon for notification. Icon must be of :type and maximum :size. In order to optimize (reduce image filesize) <a href=":link">please visit this site</a>.',
    'notification_slug'      => 'Use simple descriptive word, without spaces and special characters. Maximum :amount characters.',
    'notification_name'      => 'Use short descriptive words. Maximum :amount characters.',
    'notification_important' => 'Determine whether notification is considered important.',

    // Recipe|RecipeDistribution|RecipeCategory
    'related_recipes_help_text' => 'In order to search for recipes, type in recipe ID or recipe title.',

    // Ingredients
    'ingredients_help_text'     => 'In order to select required ingredients type name or ID to search them first.',
    'ingredient_tags_help_text' => 'In order to select required tags type "title" or "id" to search them first.',

    // Notification
    'notification_type'      => 'Select corresponding notification type.',
    'notification_attribute' => 'Fill :attribute for both languages.',
    'notification_link'      => [
        'title' => 'To save the link, please, fill in title for both languages and the URL itself. If you would like to skip it, just leave all fields blank.',
        'url'   => 'Please enter a valid URL.',
    ],

    // Recipe tags
    'recipe_tag' => [
        'publicFlag' => 'Set this tag as available for public visibility and filtering',
        'randomFlag' => 'Set this tag as available for admins only',
    ],

    'admins' => [
        'liable_clients' => 'In order to search for users, type in User ID',
    ],
];

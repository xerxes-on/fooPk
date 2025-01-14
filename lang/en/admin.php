<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Helpers Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain translations for admin panel.
    |
    */

    'sidebar' => [
        'recipe_tag'   => 'Recipe tag',
        'recipe_scope' => 'Recipes scope',
    ],

    // Ingredients section
    'ingredients' => [
        'label' => [
            'all_ingredients'          => 'All ingredients',
            'select_ingredient'        => 'Select ingredient',
            'type_title_of_ingredient' => 'Type in the title of ingredient',
        ],
    ],

    // Ingredient tag section
    'ingredient_tag' => [
        'title'                        => 'Ingredient tag',
        'type_title_of_ingredient_tag' => 'Type in the title or slug of ingredient tag',
    ],

    'ingredient_hint' => [
        'title' => 'Ingredient Hint',
    ],

    // Recipes section
    'recipes' => [
        'type_title_of_recipe' => 'Type in the title of recipe',
        'translations_done'    => 'Translations done',
    ],

    // Recipe tag section
    'recipe_tag' => [
        'title'                    => 'Recipe tag',
        'publicFlag'               => 'Available in app',
        'internalFlag'             => 'Available in admin panel',
        'type_title_of_recipe_tag' => 'Type in the title or slug of recipe tag',
    ],

    // Client Filters
    'filters' => [
        'defaults' => [
            'select'          => 'Please select',
            'missing'         => 'No',
            'multiple_active' => 'Multiple',
            'exist'           => 'Yes',
        ],
        'formular' => [
            'title'   => 'Formular approved',
            'options' => [
                'approved'     => 'Yes',
                'not_approved' => 'No',
                'missing'      => 'Missing',
            ]
        ],
        'subscription' => [
            'title' => 'Subscription active',
        ],
        'chargebee_subscription' => [
            'title' => 'Chargebee subscription active',
        ],
        'status' => [
            'title'   => 'Enabled',
            'options' => [
                'active'   => 'Yes',
                'disabled' => 'No',
            ]
        ],
        'newsletter' => [
            'title' => 'Newsletter',
        ],
        'challenge' => [
            'title' => 'Active course',
        ],
        'language' => [
            'title' => 'Language',
            'en'    => 'English',
            'de'    => 'German',
        ],
        'consultant' => [
            'title'   => 'Consultant',
            'options' => [
                'missing' => 'Not present',
                'any'     => 'Is present',
            ]
        ],
    ],

    'buttons' => [
        'new_record'         => 'New entry',
        'add_random_recipes' => 'Add random recipes',
        'new_article'        => 'Add Article',
        'reset'              => 'Reset',
    ],

    'admins' => [
        'fields' => [
            'liable_clients' => 'Liable users',
        ],
    ],

    'clients' => [
        'challenges' => [
            'messages' => [
                'success' => 'Course has been added successfully.',
            ]
        ],
        'fields' => [
            'liable_admin'              => 'Responsible consultant: :name',
            'automatic_meal_generation' => 'Automatic meal plan generation',
        ]
    ],

    'challenges' => [
        'tab_title' => 'Course info',
    ],

    'articles' => [
        'tab_title'   => 'Lessons',
        'table_title' => 'Daily lessons',
        'id_field'    => 'Lesson-ID',
    ],

    'tags' => 'Tags',

    // Questionnaire
    'questionnaire' => [
        'labels' => [
            'question'            => 'Question',
            'name'                => 'Name',
            'date'                => 'Date',
            'creator'             => 'Creator',
            'creation_method'     => 'Creation method',
            'action'              => 'Action',
            'answer'              => 'Answer',
            'compare_answer'      => 'Compare Answer',
            'compare_formular'    => 'Compare Formular',
            'current'             => 'Current',
            'base_questions'      => 'Base questions',
            'marketing_questions' => 'Marketing questions',
            'history'             => 'Questionnaire history',
        ],
        'messages' => [
            'error' => [
                'no_history' => 'No questionnaire history found.',
            ]
        ],
        'buttons' => [
            'edit'                => 'Edit',
            'create'              => 'Create',
            'approve'             => 'Approve formular',
            'enable_user_editing' => 'Force visibility for user',
        ]
    ],

    // various messages
    'messages' => [
        'confirmation'                 => 'Are you sure?',
        'revert_warning'               => 'You will not be able to revert this!',
        'revert_info'                  => 'This action could be reversed at any time',
        'saved'                        => 'Your work has been saved!',
        'wait'                         => 'Please Wait!',
        'in_progress'                  => 'Is working...',
        'record_blocked_by_job'        => 'Record is blocked due to background tasks running in the background',
        'record_blocked_by_dependency' => 'Record is blocked due to presence in recipes. Please check the following recipes first: :recipes',
        'no_user_selected'             => 'No User selected',
        'error'                        => 'Error',
        'something_went_wrong'         => 'Something went wrong',
        'subscription_id'              => 'Enter Chargebee subscription id',
        'changes_applied'              => 'Changes has been applied',
        'confirm_details'              => 'Confirm details?',
        'subscription_stopped'         => 'Active subscription will be stopped!',
        'success'                      => 'Success!',
        'delete_all_recipes_user'      => 'Are you sure to delete all recipes for this user?',
        'randomize_recipes_settings'   => 'Randomize recipes settings',
        'no_item_selected'             => 'No item selected',
        'deleted'                      => 'Deleted',
        'no_item'                      => 'No item',
        'are_you_sure'                 => 'Are you sure to delete selected recipes for this user? ',
        'fp_count_message'             => 'How many FP do you want to add?',
    ],
];

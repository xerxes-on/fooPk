<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used in various email templates.
    |
    */

    'formular' => [
        'missing' => [
            'subject'               => 'Your questionnaire is waiting for you',
            'greeting'              => 'Hello :name,',
            'line1'                 => 'your questionnaire is waiting for you! Fill it out to receive your personalized meal plan.',
            'line2'                 => 'Click here to set your password and then fill out the questionnaire:',
            'reset_password_button' => 'Set password',
            'line3'                 => 'Please note that the link is valid for :amount days.',
            'line4'                 => 'Once you have completed the questionnaire, you will shortly receive your individual meal plan in the Foodpunk App.',
            'line5'                 => 'If you have any questions, our customer support team will be happy to assist you.',
            'line6'                 => 'We look forward to guiding you on your journey to healthier eating!',
            'regards'               => 'Best regards,',
            'team'                  => 'Your Foodpunk Team',
        ],
    ],

    'footer' => [
        'contact'      => 'Contact',
        'chat'         => 'Chat on homepage',
        'address'      => 'Imprint',
        'full_address' => 'Foodpunk GmbH | Georg-Knorr-Str. 21 | 85662 Hohenbrunn | Germany',
    ],

    'verification' => [
        'title'             => 'Verify Email Address',
        'verification_line' => 'Please click the button below to verify your email address.',
        'action_link'       => 'Verify Email',
        'no_further_action' => 'If you did not create an account, no further action is required.',
    ],

    'greeting_user' => 'Hello :name,',
    'action_text'   => "If you have difficulty clicking the \":actionText\" button, copy and paste the following URL into your web browser:",
    'userReactivationUser'=>[
        'mail_subject'=>'Your Foodpunk account has been reactivated',
        'hello_username'=>'Hello :name,',
        'line1'=>'We\'re glad to have you back - welcome!',
        'line2'=>'As you were already a customer with us, your access to your nutrition programme has been fully reactivated. You can now log in to the app or <a href="https://meinplan.foodpunk.de/">meinplan.foodpunk.de<a/> with your email address and access all your recipes.',
        'line3'=>'If you no longer know your password, please use the ‘<a href="https://meinplan.foodpunk.de/password/reset">Forgot password</a>’ function.',
        'line4'=>'Your details in the questionnaire are probably a little out of date now. Therefore, you can now call up your questionnaire directly after your login via the menu and change your data there. Your meal plan will then be adapted to your new details.',
        'line5'=>'Feel free to contact us at any time if you have any questions. We wish you every success with your re-entry.',
        'line6'=>'Team Foodpunk',
    ],
    'userReactivationAdminSuccess'=>[
        'mail_subject'=>'Automatic account reactivation',
        'meal_plan_has_been_reactivated'=>'Meal plan of user <a href="https://static.foodpunk.de/admin/users/:userId/edit"><b>:userEmail</b></a> has been reactivated by automation :reactivationDate',
        'current_plan_id'=>' Current plan_id = ":planId"',
        'list_of_added_courses'=>'List of added courses: :addedCoursesStr',
    ],
    'userReactivationAdminNotAllowed'=>[
        'mail_subject'=>'Chargebee order, account duplication found',
        'line1'=>'Account with email: <a href="https://static.foodpunk.de/admin/users/:userId/edit"><b>:userEmail</b></a> already exists, please check if a new account needs to be created.',
        'line2'=>'Order with ID: :orderId made by user [:firstName, :lastName] ',
        'line3'=>'Current plan_id = ":planId"',
        'conditions'=>'Conditions',
    ],
];

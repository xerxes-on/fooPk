<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Questionnaire Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used in Questionnaire to provide content
    | to questions and other related stuff.
    |
    */

    'questions' => [
        'main_goal' => [
            'title'   => 'What is your main goal?',
            'options' => [
                'lose_weight'     => 'Lose weight',
                'healthy_weight'  => 'Eat better and maintain a healthy weight',
                'improve_fitness' => 'Improve fitness',
                'improve_health'  => 'Improve health',
                'gain_weight'     => 'Gain weight',
                'build_muscle'    => 'Build muscle',
            ]
        ],
        'weight_goal' => [
            'title'   => 'What is your weight goal?',
            'options' => [
                'weight_goal' => 'Enter weight goal in kg',
            ],
            'validation_error' => 'Please choose a realistic weight goal',
        ],
        'extra_goal' => [
            'title'   => 'Do you have any additional goals?',
            'options' => [
                'improve_daily_energy'      => 'Feel fitter everyday life',
                'reduce_body_fat'           => 'Reduce body fat',
                'build_muscle'              => 'Build muscle',
                'become_defined'            => 'Become more defined',
                'improve_skin'              => 'Improve skin appearance',
                'improve_intestine'         => 'Improve intestinal health',
                'improve_immune'            => 'Improve immune system',
                'improve_sleep'             => 'Improve sleep quality',
                'improve_food_relationship' => 'Improving relationship with food',
            ]
        ],
        'first_name' => [
            'title'   => 'What name may we call you?',
            'options' => [
                'first_name' => 'Enter your name',
            ],
        ],
        'info_welcome' => [
            'title'   => 'Hello :name,',
            'options' => [
                'main_goal'   => 'we are happy to support you to :main_goal',
                'weight_goal' => ' and to reach your goal weight of :weight_goal kg.',
                'extra_goal'  => 'Your plan also can help you to :extra_goal.',
                'end'         => 'An exiting journey lies before you and you will feel so much happier and more energized!',
            ],
            'replaces' => [
                'main_goal' => [
                    'lose_weight'     => 'lose weight in a healthy and delicious way',
                    'healthy_weight'  => 'eat healthy and nourish your body',
                    'improve_fitness' => 'increase your fitness level',
                    'improve_health'  => 'improve your overall health',
                    'gain_weight'     => 'gain weight in a healthy way',
                    'build_muscle'    => 'build muscle effectively',
                ],
                'extra_goal' => [
                    'improve_daily_energy'      => 'feel fitter',
                    'reduce_body_fat'           => 'reduce body fat',
                    'build_muscle'              => 'build muscle in combination with strength training',
                    'become_defined'            => 'define your body',
                    'improve_skin'              => 'improve your skin',
                    'improve_intestine'         => 'get your digestion under control',
                    'improve_immune'            => 'improve your immunity',
                    'improve_sleep'             => 'sleep better',
                    'improve_food_relationship' => 'learn to appreciate healthy and delicious food that is good for your body',
                ]
            ]
        ],
        'main_goal_reason' => [
            'title'    => 'Why do you want to :reason?',
            'subtitle' => 'None of this applies to you? Click Next.',
            'options'  => [
                'improve_health'          => 'To improve my health',
                'event_preparation'       => 'To prepare for an event',
                'boost_confidence'        => 'To feel more confident',
                'improve_metabolism'      => 'To burn more calories',
                'prevent_age_muscle_loss' => 'To counteract age-related muscle loss',
                'improve_fitness'         => 'To become fitter',
            ],
        ],
        'circumstances' => [
            'title'   => 'How would you describe your current circumstances?',
            'options' => [
                'fast_food'                => 'I often rely on food on the road',
                'regular_cooking'          => 'I can cook for myself regularly',
                'cooking_additionally'     => 'I cook for additional people in the household',
                'supportive_environment'   => 'My personal environment supports me',
                'unsupportive_environment' => 'My personal environment does not tend to support me',
                'neutral_environment'      => 'My personal environment is rather neutral',
            ],
        ],
        'sociability' => [
            'title'   => 'What applies to you the most?',
            'options' => [
                'alone'     => 'I prefer to go through this journey alone',
                'community' => 'The communication with like-minded people is important to me',
            ],
        ],
        'info_support' => [
            'title'   => '',
            'options' => [
                'alone'             => 'You can follow your diet plan on your own!',
                'community'         => "The exclusive Foodpunk Community is waiting for you! There you'll meet hundreds of Foodpunks to share your experiences.",
                'alone_support'     => "However, Foodpunk's expert support is always available for you via live chat, email and phone.",
                'community_support' => "Foodpunk's expert support is always ready to help you via live chat, email and phone."
            ],
        ],
        'difficulties' => [
            'title'   => 'What is currently the biggest difficulty for you?',
            'options' => [
                'change_habits'         => 'Changing habits',
                'deal_cravings'         => 'To deal with cravings',
                'deal_emotional_eating' => 'To deal with emotional eating',
                'deal_overeating'       => 'To say "no" when food is offered to me',
                'time_absence'          => 'I do not have enough time',
                'none'                  => 'None of the above',
            ],
        ],
        'lifestyle' => [
            'title'   => 'What does your everyday activity look like?',
            'options' => [
                'mainly_lying'     => 'Primarily lying down (e.g. in hospital)',
                'mainly_sitting'   => 'Primarily sitting (e.g. office work)',
                'sitting_standing' => 'Sitting/Standing (e.g. housewife/-husband, nursing, doctor)',
                'standing_waking'  => 'Standing/Walking (e.g. retail worker)',
                'active'           => 'Very active (e.g. construction worker, professional athlete)',
            ],
        ],
        'diets' => [
            'title'    => 'Do you want to eat specific diet?',
            'subtitle' => 'You can combine several diets.',
            'options'  => [
                'ketogenic'     => 'Ketogenic',
                'low_carb'      => 'Low carb',
                'moderate_carb' => 'Moderate carb',
                'paleo'         => 'Paleo',
                'vegetarian'    => 'Vegetarian',
                'vegan'         => 'Vegan',
                'pascetarian'   => 'Pescetarian',
                'aip'           => 'Autoimmune Protocol (AIP)',
                'any'           => 'Does not matter as long as I reach my goal',
            ],
            'tooltip' => [
                'ketogenic'     => '30 g Carbohydrates per day as well as lots of healthy fats and high-quality protein, tailored to your needs. Ideal if you want to lose a lot of weight or follow a ketogenic diet for health reasons.',
                'low_carb'      => '50 g Carbohydrates per day as well as lots of healthy fats and high-quality protein, tailored to your needs. Ideal if you want to avoid blood sugar fluctuations.',
                'moderate_carb' => '100 g Carbohydrates per day as well as lots of healthy fats and high-quality protein, tailored to your needs. Ideal if you do a lot of sport.',
                'paleo'         => 'Without dairy products, a combination with a vegetarian option is not possible.',
                'pascetarian'   => 'Without meat products, but with fish and sea food products.',
                'vegetarian'    => 'Without meat and fish products, with dairy products, therefore a combination with paleo is not possible.',
                'vegan'         => 'Without animal products, a combination with paleo and AIP is not possible.',
                'aip'           => 'Without dairy products, eggs, nightshades, nuts, seeds and legumes. A combination with the vegetarian or vegan option is not possible.',
            ],
        ],
        'meals_per_day' => [
            'title'   => 'How many meals do you want to eat?',
            'options' => [
                'full_3'           => '3 meals (breakfast, lunch, dinner)',
                'breakfast_lunch'  => '2 meals (breakfast and lunch)',
                'breakfast_dinner' => '2 meals (breakfast and dinner)',
                'lunch_dinner'     => '2 meals (lunch and dinner)',
            ],
        ],
        'allergies' => [
            'title'    => 'Do you have intolerances or allergies?',
            'subtitle' => "If you don't want to eat specific foods, you can exclude them in the next step.",
            'tooltip'  => [
                'hist'   => 'Choose this when you are medically diagnosed with histamine intolerance. If you only cannot eat a few specific foods that are high in histamine, such as cheese, please exclude them separately in the next step.',
                'oxalic' => 'Oxalate is a natural acid that is found in some fruits and vegetables. A diet low in oxalate is indicated for people suffering from kidney diseases.',
            ],
        ],
        'exclude_ingredients' => [
            'title'   => 'Do you not want to eat certain additional foods?',
            'options' => [
                'exclude_ingredients' => 'Enter minimum 3 letters'
            ],
        ],
        'info_security' => [
            'title'   => '',
            'options' => [
                'info'  => 'At Foodpunk your personal data is safe. They are encrypted and stored on a server in Germany.',
                'extra' => 'They are only used to create your personal nutrition plan and for individual advice from our experts.'
            ],
        ],
        'email' => [
            'title'   => 'Email',
            'options' => [
                'email'              => '',
                'subscribe_checkbox' => 'I want to receive news from Foodpunk',
            ],

        ],
        'info_testimonials' => [
            'title'   => '',
            'options' => [
            ],
        ],
        'sports' => [
            'title'   => 'How much sports do you do?',
            'options' => [
                'easy'      => 'Easy sports',
                'medium'    => 'Medium intense sports',
                'intensive' => 'Very intensive sports',
                'frequency' => 'Times per week',
                'duration'  => 'Duration of 1 workout in minutes',
            ],
            'tooltip' => [
                'easy'      => 'Low intensity units, e.g. easy yoga, stretching, brisk walks, riding the bicycle in everyday life',
                'medium'    => 'Moderate endurance training, e.g. running, easy weight training, intense yoga training',
                'intensive' => 'Training with a high afterburn effect, e.g. intensive weight training, sprints, HIIT',
            ],
            'validation_errors' => [
                'frequency' => 'Training frequency must be between 1 and 7 times per week',
                'duration'  => 'Training duration must be within 120 minutes',
            ],
            'formatted_answer' => ':type: :frequency times a week, :duration minutes per workout',
        ],
        'recipe_preferences' => [
            'title'   => 'What is most important for you?',
            'options' => [
                'quick_meals'     => 'Fast prepared recipes',
                'meal_prep'       => 'Recipes for meal prep',
                'cost_effective'  => 'Low budget recipes',
                'family_friendly' => 'Family-friendly recipes',
                'any'             => 'I have no special requirements'
            ],
            'tooltip' => [
                'quick_meals'     => 'Perfect, if you don’t have much time but love to cook fresh',
                'meal_prep'       => 'For all, who like to cook bigger amounts. A lot of dishes can be stored in the fridge for several days or can be easily frozen',
                'cost_effective'  => 'Great for a pocket-friendly grocery shopping. Expensive groceries like meat are rare in your meal plan',
                'family_friendly' => 'Your family will love these recipes!',
            ]
        ],
        'diseases' => [
            'title' => 'Do you suffer from any health conditions?',
        ],
        'motivation' => [
            'title'   => 'How do you feel starting your journey?',
            'options' => [
                'motivated' => 'Motivated',
                'confident' => 'Confident',
                'excited'   => 'Excited',
                'sceptical' => 'Sceptical',
                'insecure'  => 'Insecure',
            ],
        ],
        'info_motivation' => [
            'title'             => "That's exciting news!",
            'title_alternative' => "You're not alone in this",
            'options'           => [
                'info_negative'  => "Starting anew can be tough, but we're here to help you.",
                'extra_negative' => "Countless individuals achieved their goals with Foodpunk. And now, it is your chance to shine!",
                'info_positive'  => 'With our assistance, nothing will be able to hold you back.',
                'extra_positive' => 'Every day, countless individuals achieve their goals with Foodpunk. Ready to join them?',
            ],
        ],
        'info_team_details' => [
            'title'   => 'Find out more about Foodpunk and the team',
            'options' => [
                'info' => 'The Foodpunk nutritionists create your perfect plan only for you. No matter if your target is reducing weight, getting fitter or healthy or if you want to eat variedly despite allergies and intolerances: Barbara and the Foodpunk Team make it possible for you for you. You will cook and feast - we make sure that you will reach your target easily.'
            ],
        ],
        'gender' => [
            'title'   => 'What sex were you assigned at birth?',
            'options' => [
                'male'   => 'Male',
                'female' => 'Female',
            ],
        ],
        'birthdate' => [
            'title'   => 'What is your date of birth?',
            'options' => [
            ],
            'validation_errors' => [
                'min_age' => 'You must be at least 16 years old to use Foodpunk',
                'max_age' => 'You must be under 100 years old to use Foodpunk',
            ],
        ],
        'height' => [
            'title'   => 'What is your height?',
            'options' => [
                'height' => 'Enter height in cm',
            ],
        ],
        'weight' => [
            'title'   => 'What is your weight?',
            'options' => [
                'weight' => 'Enter weight in kg',
            ],
        ],
        'fat_content' => [
            'title'    => 'What is your body fat percentage?',
            'subtitle' => "If you don't know it, click Next",
            'options'  => [
                '<15%'   => '< 15%',
                '16_20%' => '16 - 20 %',
                '21_30%' => '21 - 30 %',
                '>31%'   => '> 31%',
            ],
        ],
        'features' => [
            'title'   => 'What Foodpunk feature are you looking forward the most?',
            'options' => [
                'shopping_list'     => 'Convenient 1-click shopping list',
                'community'         => 'Motivating Foodpunk community',
                'support'           => 'Sincere expert support',
                'weekly_plan'       => 'Super-flexible weekly plan',
                'recipes_to_needs'  => 'Delicious recipes tailored to my nutrient needs',
                'challenges'        => 'Motivating challenges',
                'knowledge_content' => 'Valuable knowledge content',
                'seasonal_recipes'  => 'Regularly new seasonal recipes',
            ],
        ],

    ],

    'validation' => [
        'answer' => [
            'structure' => 'The answer response is not valid',
            'value'     => 'Answer value is not recognized',
            'empty'     => 'Please pick an option',
            'missing'   => 'Please answer the question'
        ],
        'email' => [
            'unique' => 'This email has already been taken.'
        ],
        'weight' => [
            'min' => 'Please enter a value over :min kg.',
            'max' => 'Please enter a value lower than :max kg',
        ],
        'height' => [
            'min' => 'Please enter a value over :min cm',
            'max' => 'Please enter a value lower then :max cm',
        ],
    ],

    // Special word separator for info pages
    'info_pages' => [
        'text_separator' => ' and '
    ],

    // Page title displayed in across the app
    'page_title' => 'Questionnaire',

    // Various info messages
    'info' => [
        'temporary_saved'     => 'Confirm your email within 3 days to save your questionnaire.',
        'insufficient_fund'   => 'Top up your Foodpoints account to change your questionnaire.',
        'update_confirmation' => 'Would you really like to update your questionnaire?',
        'withdraw_error'      => 'Unable to withdraw Foodpoints from your account. Please try again later',
        'not_saved_error'     => 'Data is not saved. Please try again later',
        'withdraw'=> 'Withdraw',
        'withdraw_number' =>'How many CS do you want to withdraw?',
    ],

    // Button texts
    'buttons' => [
        'edit_for_fp' => 'Edit for Foodpoints',
    ]
];

<?php

declare(strict_types=1);

return [
    'distributions' => [
        // @deprecated
        'weekly' => [
            'start_at' => '2024-08-09 00:00:00',
            'pushnotification' => [
                'texts' => [
                    'en' => [
                        'title' => 'Fresh Foodpoints for you! 💰 ',
                        'content' => 'Your weekly Foodpoints are here. Use them to find new recipes in the marketplace or for your next challenge. 💪',
                    ],
                    'de' => [
                        'title' => 'Neue Foodpunkte für dich! 💰 ',
                        'content' => 'Deine wöchentlichen Foodpunkte sind da. Such dir damit gleich neue Rezepte auf dem Marktplatz oder deine nächste Challenge. 💪',
                    ]
                ]
            ],
            'deposit_text' => 'Weekly foodpoints',
            'amount' => 20,
            'checkpoint_period' => 7, // period in days, starts from registration date
        ],
        'monthly' => [
            'start_at' => '2024-11-13 00:00:00',
            'pushnotification' => [
                'texts' => [
                    'en' => [
                        'title' => 'Fresh Foodpoints for you! 💰 ',
                        'content' => 'Your monthly Foodpoints are here. Use them to grab new favourite recipes from the marketplace or start a new course! 💪',
                    ],
                    'de' => [
                        'title' => 'Neue Foodpunkte für dich! 💰 ',
                        'content' => 'Deine monatlichen Foodpunkte sind da. Schnapp dir damit gleich neue Lieblingsrezepte auf dem Marktplatz oder starte einen neuen Kurs!💪',
                    ]
                ]
            ],
            'deposit_text' => 'Monthly foodpoints',
            'amount' => 100,
            'checkpoint_period' => 30, // period in days, starts from registration date
        ],

    ]
];

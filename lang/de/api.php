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
            'email_not_verified'     => 'E-Mail noch nicht bestätigt.',
            'email_already_verified' => 'E-Mail wurde bereits bestätigt.',
            'incorrect_creds'        => 'Die angegebenen Zugansdaten sind nicht korrekt.',
        ],
        'success' => [
            'email_verified'          => 'E-Mail bestätigt.',
            'logout_success'          => 'Du bist nun abgemeldet.',
            'login_success'           => 'Erfolgreich angemeldet.',
            'email_verification_sent' => 'Du hast nun eine E-Mail zur Bestätigung deiner E-Mail-Adresse erhalten.',
        ],
        'registration' => [
            'success' => [
                'user_created' => 'Neuer Nutzer erfolgreich angelegt.',
            ],
        ],
    ],
    'incorrect_creds' => 'Die angegebenen Zugansdaten sind nicht korrekt.',
    'logout_success'  => 'Du bist nun abgemeldet.',
    'login_success'   => 'Erfolgreich angemeldet.',

    // Chargebee
    'chargebee_sync_success' => 'Chargebee sync erfolgreich.',

    // Flexmeal
    'flexmeal_404'           => 'Das gewählte FlexMeal existiert nicht.',
    'flexmeal_updated'       => 'Aktualisiert',
    'flexmeal_image_updated' => 'Bild erfolgreich geändert',

    // Formular
    'formular_edit_prohibited'   => 'Der Fragebogen kann nicht geändert werden.',
    'withdraw_fail'              => 'Foodpunkte können nicht abgebucht werden. Bitte versuche es später erneut.',
    'amount_foodpoints_withdraw' => ':amount Foodpunkte wurden abgebucht',
    'formular_404'               => 'Keine verfügbare Anwort gefunden',
    'formular'                   => [
        'edit_check_free' => [
            'title'  => 'Möchtest du deine Daten ändern?',
            'body'   => 'Dein Ernährungsplan wird anschließend innerhalb eines Werktages auf deine neuen Daten im Fragebogen angepasst.',
            'button' => 'Daten ändern',
        ],
        'edit_check_paid' => [
            'button' => 'Freischalten',
        ]
    ],

    // Ingredients
    'ingredients_no_formular'      => 'Bitte beantworte die Frage. So wird ermittelt, welche Lebensmittel für dich geeignet sind.',
    'ingredient_type_error'        => 'Zutatentyp wird nicht unterstützt.',
    'ingredient_replacement_error' => 'Zutat kann nicht ersetzt werden.',

    // Recipes
    'meal_skipped'                          => 'Mahlzeit ausgelassen',
    'exclude_meal_public_error'             => 'Das Rezept kann nicht ausgeblendet werden. Bitte versuche es später erneut oder kontaktiere das Support-Team.',
    'remove_from_excluded_public_error'     => 'Dieses Rezept ist nicht ausgeblendet.',
    'exclude_success_response'              => 'Rezept ausgeblendet und ersetzt.',
    'remove_from_excluded_success_response' => 'Das Rezept ist wieder für dich verügbar.',
    'recipe_type_error'                     => 'Dies ist kein Baukasten-Rezept.',

    // Challenges
    'view_more'     => 'Mehr...',
    'buy_challenge' => 'Kurs freischalten',
    'cancel'        => 'Abbrechen',

    // Posts
    'no_posts' => 'Kein Beitrag vorhanden.',
    'success'=> 'Success',
    'failed'=> 'Failed',
];

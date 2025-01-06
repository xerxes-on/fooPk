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
            'subject'               => 'Dein Fragebogen wartet auf dich',
            'greeting'              => 'Hallo :name,',
            'line1'                 => 'dein Fragebogen wartet auf dich! Fülle ihn aus, um deinen individuellen Ernährungsplan zu erhalten.',
            'line2'                 => 'Klicke hier, um dein Passwort festzulegen und anschließend den Fragebogen auszufüllen:',
            'reset_password_button' => 'Passwort festlegen',
            'line3'                 => 'Bitte beachte, dass der Link :amount Tage lang gültig ist.',
            'line4'                 => 'Sobald du den Fragebogen ausgefüllt hast, erhältst du in Kürze deinen maßgeschneiderten Ernährungsplan in der Foodpunk App.',
            'line5'                 => 'Falls du irgendwelche Fragen hast, steht dir unser Kundensupport-Team gerne zur Verfügung.',
            'line6'                 => 'Wir freuen uns darauf, dich bei deiner Reise zu einer gesünderen Ernährung zu unterstützen!',
            'regards'               => 'Viele Grüße,',
            'team'                  => 'Dein Foodpunk-Team',
        ],
    ],

    'footer' => [
        'contact'      => 'Kontakt:',
        'chat'         => 'Live Chat auf der Homepage',
        'address'      => 'Impressum:',
        'full_address' => 'Foodpunk GmbH | Georg-Knorr-Str. 21 | 85662 Hohenbrunn',
    ],

    'verification' => [
        'title'             => 'Jetzt E-Mail-Adresse bestätigen',
        'verification_line' => 'Bitte klicke auf den Button, um deine E-Mail-Adresse zu bestätigen.',
        'action_link'       => 'E-Mail bestätigen',
        'no_further_action' => 'Falls du dir keinen Account erstellt hast, sind keine weiteren Schritte notwendig.',
    ],

    'greeting_user' => 'Hallo :name,',
    'action_text'   => "Solltest du den \":actionText\" Button nicht anklicken können, kopiere den folgenden Link und füge ihn in die Adresszeile deines Browsers ein.",
    'userReactivationUser'=>[
        'mail_subject'=>'Dein Foodpunk-Account wurde reaktiviert',
        'hello_username'=>'Hallo :name,',
        'line1'=>'es freut uns, dass du wieder dabei bist – herzlich willkommen zurück!',
        'line2'=>'Da du bereits bei uns Kunde warst, wurde dein Zugang zu deinem Ernährungsprogramm wieder vollständig reaktiviert. Du kannst dich mit deiner E-Mail-Adresse nun wieder in der App oder unter <a href="https://meinplan.foodpunk.de/">meinplan.foodpunk.de<a/> einloggen und auf alle deine Rezepte zugreifen.',
        'line3'=>'Solltest du dein Passwort nicht mehr wissen, benutze bitte die "<a href="https://meinplan.foodpunk.de/password/reset">Passwort vergessen</a>"-Funktion.',
        'line4'=>'Vermutlich sind deine Angaben im Fragebogen nun etwas veraltet. Daher kannst du nun direkt nach deinem Login über das Menü deinen Fragebogen aufrufen und dort deine Daten ändern. Dein Ernährungsplan wird anschließend auf deine neuen Angaben angepasst.',
        'line5'=>'Melde dich gerne jederzeit bei uns, wenn du Fragen dazu hast. Wir wünschen dir viel Erfolg bei deinem Wiedereinstieg.',
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

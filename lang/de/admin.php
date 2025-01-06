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
        'recipe_tag'   => 'Rezeptkategorien',
        'recipe_scope' => 'Alle Rezepte',
    ],

    // Ingredients section
    'ingredients' => [
        'label' => [
            'all_ingredients'          => 'Alle Zutaten',
            'select_ingredient'        => 'Zutat auswählen',
            'type_title_of_ingredient' => 'Zutatennamen eingeben',
        ],
    ],

    // Ingredient tag section
    'ingredient_tag' => [
        'title'                        => 'Zutaten-Tag',
        'type_title_of_ingredient_tag' => 'Titel oder Slug des Zutaten-Tags eingeben',
    ],

    'ingredient_hint' => [
        'title' => 'Zutateninformation',
    ],

    // Recipes section
    'recipes' => [
        'type_title_of_recipe' => 'Gib den Titel des Rezepts ein',
        'translations_done'    => 'Übersetzung fertig',
    ],

    // Recipe tag section
    'recipe_tag' => [
        'title'                    => 'Rezeptkategorien',
        'publicFlag'               => 'In der App verfügbar',
        'internalFlag'             => 'Im Admin Panel verfügbar',
        'type_title_of_recipe_tag' => 'Titel oder Slug des Rezept-Tags eingeben',
    ],

    // Client Filters
    'filters' => [
        'defaults' => [
            'select'          => 'Bitte auswählen',
            'missing'         => 'Nein',
            'exist'           => 'Ja',
            'multiple_active' => 'Mehrere',
        ],
        'formular' => [
            'title'   => 'Fragebogen bestätigt',
            'options' => [
                'approved'     => 'Ja',
                'not_approved' => 'Nein',
                'missing'      => 'Nicht vorhanden',
            ]
        ],
        'subscription' => [
            'title' => 'Mitgliedschaft aktiv',
        ],
        'chargebee_subscription' => [
            'title' => 'Chargebee Mitgliedschaft aktiv',
        ],
        'status' => [
            'title'   => 'Zugriff aktiviert',
            'options' => [
                'active'   => 'Ja',
                'disabled' => 'Nein',
            ]
        ],
        'newsletter' => [
            'title' => 'Newsletter',
        ],
        'challenge' => [
            'title' => 'Aktiver Kurs',
        ],
        'language' => [
            'title' => 'Sprache',
            'en'    => 'Englisch',
            'de'    => 'Deutsch',
        ],
        'consultant' => [
            'title'   => 'Betreuer',
            'options' => [
                'missing' => 'Nicht vorhanden',
                'any'     => 'Vorhanden',
            ]
        ],
    ],

    'buttons' => [
        'new_record'         => 'Neuer Eintrag',
        'add_random_recipes' => 'Zufällige Rezepte hinzufügen',
        'new_article'        => 'Beitrag verknüpfen',
        'reset'              => 'Zurücksetzen'
    ],

    'admins' => [
        'fields' => [
            'liable_clients' => 'Klienten',
        ],
    ],

    'clients' => [
        'challenges' => [
            'messages' => [
                'success' => 'Kurs erfolgreich hinzugefügt',
            ]
        ],
        'fields' => [
            'liable_admin'              => 'Betreuung durch: :name',
            'automatic_meal_generation' => 'Automatische Erstellung des Ernährungsplanes',
        ],
    ],

    'challenges' => [
        'tab_title' => 'Kurs Infos',
    ],

    'articles' => [
        'tab_title'   => 'Beiträge',
        'table_title' => 'Tägliche Wissensbeiträge',
        'id_field'    => 'Beitrags-ID',
    ],

    'tags' => 'Tags',

    // Questionnaire
    'questionnaire' => [
        'labels' => [
            'question'            => 'Frage',
            'name'                => 'Name',
            'date'                => 'Datum',
            'creator'             => 'Ersteller',
            'creation_method'     => 'Ursprung',
            'action'              => 'Aktion',
            'answer'              => 'Antwort',
            'compare_answer'      => 'Antwort vergleichen',
            'compare_formular'    => 'Fragebogen vergleichen',
            'current'             => 'Aktuell',
            'base_questions'      => 'Fragebogen',
            'marketing_questions' => 'Zusätzliche Angaben',
            'history'             => 'Fragebogenverlauf',
        ],
        'messages' => [
            'error' => [
                'no_history' => 'Kein Fragebogenverlauf gefunden.',
            ]
        ],
        'buttons' => [
            'edit'                => 'Bearbeiten',
            'create'              => 'Erstellen',
            'approve'             => 'Fragebogen bestätigen',
            'enable_user_editing' => 'Fragebogen zur Änderung freischalten',
        ]
    ],

    // various messages
    'messages' => [
        'confirmation'                 => 'Bist du sicher?',
        'revert_warning'               => 'Diese Aktion kann nicht rückgängig gemacht werden.',
        'revert_info'                  => 'Diese Aktion kann jederzeit rückgängig gemacht werden.',
        'saved'                        => 'Änderung wurde gespeichert!',
        'wait'                         => 'Bitte abwarten',
        'in_progress'                  => 'In Arbeit...',
        'record_blocked_by_job'        => 'Dieser Eintrag ist aufgrund von Tasks blockiert, die im Hintergrund ausgeführt werden',
        'record_blocked_by_dependency' => 'Dieser Eintrag ist wegen Vorhandenseins in Rezepten gesperrt. Bitte überprüfe zuerst die folgenden Rezepte: :recipes',
        'no_user_selected'             => 'Kein Benutzer ausgewählt',
        'error'                        => 'Fehler',
        'something_went_wrong'         => 'Etwas ist schief gelaufen',
        'subscription_id'              => 'Geben Sie die Chargebee-Abonnement-ID ein',
        'changes_applied'              => 'Änderungen wurden übernommen',
        'confirm_details'              => 'Bestätigen Sie die Details?',
        'subscription_stopped'         => 'Das aktive Abonnement wird beendet!',
        'success'                      => 'Erfolg!',
        'delete_all_recipes_user'      => 'Sind Sie sicher, dass alle Rezepte für diesen Benutzer gelöscht werden?',
        'randomize_recipes_settings'   => 'Rezepteinstellungen nach dem Zufallsprinzip anpassen',
        'no_item_selected'             => 'Kein Artikel ausgewählt',
        'deleted'                      => 'Gelöscht',
        'no_item'                      => 'Kein Artikel',
        'are_you_sure'                 => 'Sind Sie sicher, dass Sie die ausgewählten Rezepte für diesen Benutzer löschen möchten? ',
        'fp_count_message'             => 'How many FP do you want to add?',
    ],
];

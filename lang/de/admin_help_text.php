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
    'notification_icon'      => 'Upload/Wähle ein Symbol für die Benachrichtigung. Das Symbol muss :type sein und darf maximal :size haben. Zum Optimieren (Dateigröße reduzieren) <a href=":link">öffne diese Seite.</a>.',
    'notification_slug'      => 'Verwende ein einfaches, beschreibendes Wort, ohne Leer- und Sonderzeichen. Maximal :amount Zeichen.',
    'notification_name'      => 'Verwende kurze, beschreibende Wörter. Maximal :amount Zeichen.',
    'notification_important' => 'Lege fest, ob diese Benachrichtigung wichtig ist oder nicht.',

    // Recipe|RecipeDistribution|RecipeCategory
    'related_recipes_help_text' => 'Um nach verwandten Rezepten zu suchen tippe hier die Rezept-ID oder den Rezeptnamen ein.',

    // Ingredients
    'ingredients_help_text'     => 'Um verwendeten Zutaten zu suchen tippe hier die Zutaten-ID oder den Namen der Zutat ein.',
    'ingredient_tags_help_text' => 'Um die erforderlichen Tags auszuwählen, geben Sie zunächst "title" oder "id" ein, um sie zu durchsuchen.',

    // Notification
    'notification_type'      => 'Wähle den entsprechenden Benachrichtigungstyp.',
    'notification_attribute' => 'Befülle :attribute für beide Sprachen.',
    'notification_link'      => [
        'title' => 'Um den Link zu speichern, befülle den Titel für beide Sprachen und die URL selbst. Um dies zu überspringen, lasse die Felder leer.',
        'url'   => 'Bitte gib eine gültige URL ein.',
    ],

    // Recipe tags
    'recipe_tag' => [
        'publicFlag' => 'Dieser Tag ist öffentlich verfügbar',
        'randomFlag' => 'Dieser Tag ist nur für Admins verfügbar',
    ],

    'admins' => [
        'liable_clients' => 'Um nach Benutzern zu suchen, gib die Benutzer-ID ein.',
    ],
];

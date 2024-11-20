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
            'title'   => 'Was ist dein wichtigstes Ziel?',
            'options' => [
                'lose_weight'     => 'Abnehmen',
                'healthy_weight'  => 'Gesünder essen und Gewicht halten',
                'improve_fitness' => 'Fitness steigern',
                'improve_health'  => 'Gesundheit verbessern',
                'gain_weight'     => 'Gesund zunehmen',
                'build_muscle'    => 'Muskulatur aufbauen',
            ]
        ],
        'weight_goal' => [
            'title'   => 'Welches Zielgewicht möchtest du erreichen?',
            'options' => [
                'weight_goal' => 'Zielgewicht in kg eingeben',
            ],
            'validation_error' => 'Bitte wähle ein realistisches Zielgewicht',
        ],
        'extra_goal' => [
            'title'   => 'Hast du weitere Ziele?',
            'options' => [
                'improve_daily_energy'      => 'Im Alltag fitter fühlen',
                'reduce_body_fat'           => 'Körperfett abbauen',
                'build_muscle'              => 'Muskulatur aufbauen',
                'become_defined'            => 'Definierter werden',
                'improve_skin'              => 'Hautbild verbessern',
                'improve_intestine'         => 'Darmgesundheit verbessern',
                'improve_immune'            => 'Immunsystem stärken',
                'improve_sleep'             => 'Schlafqualität erhöhen',
                'improve_food_relationship' => 'Beziehung zu Essen verbessern',
            ]
        ],
        'first_name' => [
            'title'   => 'Wie dürfen wir dich nennen?',
            'options' => [
                'first_name' => 'Dein Name',
            ],
        ],
        'info_welcome' => [
            'title'   => 'Hallo :name,',
            'options' => [
                'main_goal'   => 'es freut uns sehr, dich beim Erreichen deines Ziels, :main_goal, unterstützen zu dürfen',
                'weight_goal' => ' und dein Zielgewicht von :weight_goal kg zu erreichen.',
                'extra_goal'  => 'Dein Ernährungsplan kann dir zudem dabei helfen, :extra_goal.',
                'end'         => 'Vor dir liegt eine aufregende Reise und du wirst dich viel energiegeladener und glücklicher fühlen!',
            ],
            'replaces' => [
                'main_goal' => [
                    'lose_weight'     => 'gesund und mit Genuss Gewicht zu verlieren',
                    'healthy_weight'  => 'dich gesünder zu ernähren',
                    'improve_fitness' => 'fitter zu werden',
                    'improve_health'  => 'etwas Gutes für deine Gesundheit zu tun',
                    'gain_weight'     => 'gesund zuzunehmen',
                    'build_muscle'    => 'effektiv Muskeln aufzubauen',
                ],
                'extra_goal' => [
                    'improve_daily_energy'      => 'dich fitter zu fühlen',
                    'reduce_body_fat'           => 'körperfett zu verlieren',
                    'build_muscle'              => 'in Kombination mit Krafttraining Muskeln aufzubauen',
                    'become_defined'            => 'deinen Körper zu definieren',
                    'improve_skin'              => 'dein Hautbild zu verbessern',
                    'improve_intestine'         => 'deine Verdauung in den Griff zu bekommen',
                    'improve_immune'            => 'dein Immunsystem zu stärken',
                    'improve_sleep'             => 'besser zu schlafen',
                    'improve_food_relationship' => 'gutes und gesundes Essen wertschätzen zu lernen',
                ]
            ],
        ],
        'main_goal_reason' => [
            'title'    => 'Warum möchtest du :reason?',
            'subtitle' => 'Nichts davon trifft auf dich zu? Klick auf Weiter.',
            'options'  => [
                'improve_health'          => 'Um meine Gesundheit zu verbessern',
                'event_preparation'       => 'Als Vorbereitung auf ein Event',
                'boost_confidence'        => 'Um mich selbstbewusster zu fühlen',
                'improve_metabolism'      => 'Um mehr Kalorien zu verbrennen',
                'prevent_age_muscle_loss' => 'Um altersbedingtem Muskelabbau entgegen zu wirken',
                'improve_fitness'         => 'Um fitter zu sein',
            ],
        ],
        'circumstances' => [
            'title'   => 'Wie würdest du deine Umstände beschreiben?',
            'options' => [
                'fast_food'                => 'Ich bin häufig auf Essen unterwegs angewiesen',
                'regular_cooking'          => 'Ich kann regelmäßig für mich selbst kochen',
                'cooking_additionally'     => 'Ich versorge zusätzlich weitere Personen im Haushalt',
                'supportive_environment'   => 'Mein Umfeld unterstützt mich',
                'unsupportive_environment' => 'Mein Umfeld unterstützt mich eher nicht',
                'neutral_environment'      => 'Mein Umfeld ist eher neutral',
            ],
        ],
        'sociability' => [
            'title'   => 'Was trifft am ehesten auf dich zu?',
            'options' => [
                'alone'     => 'Ich ziehe meine Pläne am liebsten alleine durch',
                'community' => 'Der Austausch mit Gleichgesinnten ist mir sehr wichtig',
            ],
        ],
        'info_support' => [
            'title'   => '',
            'options' => [
                'alone'             => 'Deinen Ernährungsplan kannst du wunderbar alleine durchziehen!',
                'community'         => 'Die Foodpunk Community wartet schon auf dich! Dort triffst du hunderte Foodpunks, um dich mit ihnen auszutauschen.',
                'alone_support'     => 'Der Foodpunk Experten-Support steht dir jedoch immer per Live Chat, E-Mail und telefonisch zur Seite.',
                'community_support' => 'Der Foodpunk Experten-Support steht dir zusätzlich immer gerne per Live Chat, E-Mail und telefonisch zur Seite.'
            ],
        ],
        'difficulties' => [
            'title'   => 'Was fällt dir derzeit noch am schwersten?',
            'options' => [
                'change_habits'         => 'Gewohnheiten zu verändern',
                'deal_cravings'         => 'Mit meinem Heißhunger umzugehen',
                'deal_emotional_eating' => 'Mit emotionalem Essen umzugehen',
                'deal_overeating'       => '“Nein” zu sagen, wenn mir Essen angeboten wird',
                'time_absence'          => 'Ich habe nicht genügend Zeit',
                'none'                  => 'Nichts davon',
            ],
        ],
        'lifestyle' => [
            'title'   => 'Wie sieht deine Aktivität im Alltag ohne Sport aus?',
            'options' => [
                'mainly_lying'     => 'vorwiegend liegend (z.B. im Krankenhaus)',
                'mainly_sitting'   => 'vorwiegend sitzend (z.B. Bürojob)',
                'sitting_standing' => 'sitzend / stehend (z.B. Hausfrau/-mann, Krankenpflege, Ärztin/Arzt)',
                'standing_waking'  => 'stehend / gehend (z.B. Verkäufer/-in)',
                'active'           => 'sehr aktiv, z.B. auf Baustellen, Profisportler/-in',
            ],
        ],
        'diets' => [
            'title'    => 'Möchtest du dich auf eine bestimmte Weise ernähren?',
            'subtitle' => 'Du kannst mehrere Ernährungsweisen kombinieren.',
            'options'  => [
                'ketogenic'     => 'Ketogen',
                'low_carb'      => 'Low carb',
                'moderate_carb' => 'Moderate carb',
                'paleo'         => 'Paleo',
                'vegetarian'    => 'Vegetarisch',
                'vegan'         => 'Vegan',
                'pascetarian'   => 'Pescetarisch',
                'aip'           => 'Autoimmunprotokoll (AIP)',
                'any'           => 'Egal, Hauptsache ich erreiche mein Ziel.',
            ],
            'tooltip' => [
                'ketogenic'     => 'Enthält 30 g Kohlenhydrate pro Tag sowie viele gesunde Fette und hochwertiges Protein, auf deinen Bedarf abgestimmt. Ideal, wenn du viel abnehmen oder dich aus gesundheitlichen Gründen ketogen ernähren möchtest.',
                'low_carb'      => 'Enthält 50 g Kohlenhydrate pro Tag sowie viele gesunde Fette und hochwertiges Protein, auf deinen Bedarf abgestimmt. Ideal, wenn du Blutzuckerschwankungen vermeiden möchtest.',
                'moderate_carb' => 'Enthält 100 g Kohlenhydrate pro Tag sowie gesunde Fette und hochwertiges Protein, auf deinen Bedarf abgestimmt. Ideal, wenn du viel Sport machst.',
                'paleo'         => 'Ohne Milchprodukte und Getreide. Keine Kombination mit vegetarischer Ernährung möglich',
                'pascetarian'   => 'Enthält kein Fleisch, jedoch Fisch und Meeresfrüchte.',
                'vegetarian'    => 'Enthält kein Fleisch und keinen Fisch, mit Milchprodukten. Keine Kombination mit Paleo möglich.',
                'vegan'         => 'Ohne tierische Produkte. Keine Kombination mit Paleo oder AIP möglich.',
                'aip'           => 'Ohne Milchprodukte, Eier, Nachtschattengewächse, Nüsse, Samen, und Hülsenfrüchte. Keine Kombination mit vegetarisch oder vegan möglich.',
            ],
        ],
        'meals_per_day' => [
            'title'   => 'Wie häufig möchtest du am Tag essen?',
            'options' => [
                'full_3'           => '3 Mahlzeiten (Frühstück, Mittag- und Abendessen)',
                'breakfast_lunch'  => '2 Mahlzeiten (Frühstück und Mittagessen)',
                'breakfast_dinner' => '2 Mahlzeiten (Frühstück und Abendessen)',
                'lunch_dinner'     => '2 Mahlzeiten (Mittag- und Abendessen)',
            ],
        ],
        'allergies' => [
            'title'    => 'Hast du Allergien oder Intoleranzen?',
            'subtitle' => 'Lebensmittel, die du nicht essen möchtest, kannst du gezielt im nächsten Schritt ausschließen.',
            'tooltip'  => [
                'hist'   => 'Wähle diesen Punkt aus, wenn du eine ärztlich diagnostizierte ausgeprägte Histaminintoleranz hast. Verträgst du nur wenige histaminreiche Lebensmittel, wie gereiften Käse, nicht, schließe sie separat im nächsten Schritt aus.',
                'oxalic' => 'Oxalat ist eine Säure, die natürlicherweise in manchen Obst- und Gemüsesorten auftritt. Eine oxalatarme Ernährung kann etwa bei Nierenerkrankungen notwendig sein.',
            ],
        ],
        'exclude_ingredients' => [
            'title'   => 'Gibt es zusätzlich bestimmte Lebensmittel, die du nicht essen möchtest?',
            'options' => [
                'exclude_ingredients' => 'Gib mind. 3 Buchstaben ein'
            ],
        ],
        'info_security' => [
            'title'   => '',
            'options' => [
                'info'  => 'Bei Foodpunk sind deine persönlichen Daten sicher. Sie werden verschlüsselt auf einem Server in Deutschland gesichert.',
                'extra' => 'Genutzt werden sie nur zur Erstellung deines persönlichen Ernährungsplanes und zur individuellen Beratung durch unsere Experten.'
            ],
        ],
        'email' => [
            'title'   => 'E-Mail',
            'options' => [
                'email'              => '',
                'subscribe_checkbox' => 'Ich möchte über Neuigkeiten von Foodpunk informiert werden.',
            ],

        ],
        'info_testimonials' => [
            'title'   => '',
            'options' => [
            ],
        ],
        'sports' => [
            'title'   => 'Wie viel Sport machst du?',
            'options' => [
                'easy'      => 'Leichter Sport',
                'medium'    => 'Anstrengender Sport',
                'intensive' => 'Sehr intensiver Sport',
                'frequency' => 'Mal pro Woche',
                'duration'  => 'Dauer einer Sporteinheit in Minuten',
            ],
            'tooltip' => [
                'easy'      => 'Sporteinheiten von geringer Intensität, z.B. leichtes Yoga, zügiges Spazierengehen, Fahrradfahren im Alltag',
                'medium'    => 'Moderates Ausdauertraining, wie Joggen, leichtes Krafttraining, intensives Yoga',
                'intensive' => 'Intensiver Sport mit hohem Nachbrenneffekt, z.B. intensives Krafttraining, Sprints, HIIT',
            ],
            'validation_errors' => [
                'frequency' => 'Die Anzahl der Einheiten muss zwischen 1 und 7 liegen',
                'duration'  => 'Bitte gib eine Dauer einer Einheit zwischen 1 und 120 Minuten an',
            ],
            'formatted_answer' => ':type: :frequency Einheiten pro Woche, :duration Minuten pro Einheit',
        ],
        'recipe_preferences' => [
            'title'   => 'Ist dir etwas an deinen Rezepten besonders wichtig?',
            'options' => [
                'quick_meals'     => 'Schnell zuzubereitende Rezepte',
                'meal_prep'       => 'Rezepte zum Vorbereiten (Meal Prep)',
                'cost_effective'  => 'Kostengünstige Rezepte',
                'family_friendly' => 'Rezepte für die ganze Familie',
                'any'             => 'Ich habe keine besonderen Anforderungen'
            ],
            'tooltip' => [
                'quick_meals'     => 'Perfekt, wenn du nicht viel Zeit hast, es aber liebst, frisch zu kochen.',
                'meal_prep'       => 'Für alle, die gerne größere Mengen vorbereiten. Viele Mahlzeiten können mehrere Tage im Kühlschrank aufbewahrt werden oder eignen sich zum Einfrieren.',
                'cost_effective'  => 'Ideal für den kleineren Geldbeutel. Der Ernährungsplan enthält weniger teurere Lebensmittel wie Fleisch.',
                'family_friendly' => 'Deine Familie wird diese Rezepte lieben!',
            ]
        ],
        'diseases' => [
            'title' => 'Hast du gesundheitliche Probleme?',
        ],
        'motivation' => [
            'title'   => 'Wie fühlst du dich gerade zu Beginn deiner Reise?',
            'options' => [
                'motivated' => 'Motiviert',
                'confident' => 'Selbstbewusst',
                'excited'   => 'Aufgeregt',
                'sceptical' => 'Skeptisch',
                'insecure'  => 'Unsicher',
            ],
        ],
        'info_motivation' => [
            'title'             => 'Wunderbar!',
            'title_alternative' => 'Du bist nicht alleine.',
            'options'           => [
                'info_negative'  => 'Ein Neuanfang kann herausfordernd sein. Wir sind da, um dich dabei zu unterstützen.',
                'extra_negative' => 'Unzählige Menschen haben mit Foodpunk ihre Ziele erreicht. Jetzt bist du dran!',
                'info_positive'  => 'Mit unserer Hilfe wird dich nun nichts mehr aufhalten können.',
                'extra_positive' => 'Jeden Tag erreichen unzählige Menschen mit Foodpunk ihre Ziele. Bist du bereit, dich ihnen anzuschließen?',
            ],
        ],
        'info_team_details' => [
            'title'   => 'Erfahre mehr über Foodpunk und das Team',
            'options' => [
                'info' => 'Die Experten bei Foodpunk erstellen deinen individuellen Ernährungsplan, nur für dich! Egal, ob du abnehmen, fitter und gesünder werden oder dich trotz Allergien und Intoleranzen abwechslungsreich ernähren möchtest: Barbara und das Foodpunk Team machen es möglich. Du musst nur kochen und genießen - wir stellen sicher, dass du dein Ziel easy und wie nebenbei erreichen wirst.'
            ],
        ],
        'gender' => [
            'title'   => 'Was ist dein biologisches Geschlecht?',
            'options' => [
                'male'   => 'Männlich',
                'female' => 'Weiblich',
            ],
        ],
        'birthdate' => [
            'title'   => 'An welchem Tag wurdest du geboren?',
            'options' => [
            ],
            'validation_errors' => [
                'min_age' => 'Du musst mindestens 16 Jahre alt sein.',
                'max_age' => 'Du musst unter 100 Jahre alt sein, um Foodpunk nutzen zu können.',
            ],
        ],
        'height' => [
            'title'   => 'Wie groß bist du?',
            'options' => [
                'height' => 'Größe in cm eingeben',
            ],
        ],
        'weight' => [
            'title'   => 'Was ist dein aktuelles Gewicht?',
            'options' => [
                'weight' => 'Gewicht in kg eingeben',
            ],
        ],
        'fat_content' => [
            'title'    => 'Wie hoch ist dein Körperfettanteil in etwa?',
            'subtitle' => 'Falls du ihn nicht weißt, klicke auf Weiter.',
            'options'  => [
                '<15%'   => '< 15%',
                '16_20%' => '16 - 20 %',
                '21_30%' => '21 - 30 %',
                '>31%'   => '> 31%',
            ],
        ],
        'features' => [
            'title'   => 'Auf welche Funktion bei Foodpunk freust du dich am meisten?',
            'options' => [
                'shopping_list'     => 'Praktische 1-Klick-Einkaufsliste',
                'community'         => 'Motivierende Foodpunk Community',
                'support'           => 'Herzlicher Kunden-Support',
                'weekly_plan'       => 'Super flexibler Wochenplan',
                'recipes_to_needs'  => 'Leckere Rezepte auf meinen Bedarf berechnet',
                'challenges'        => 'Motivierende Challenges',
                'knowledge_content' => 'Wertvolle Wissensinhalte',
                'seasonal_recipes'  => 'Regelmäßig neue saisonale Rezepte',
            ],
        ],

    ],

    'validation' => [
        'answer' => [
            'structure' => 'Die Antwort ist nicht gültig',
            'value'     => 'Der Wert deiner Antwort ist nicht bekannt',
            'empty'     => 'Bitte wähle eine Option',
            'missing'   => 'Bitte wähle eine Option'
        ],
        'email' => [
            'unique' => 'Diese E-Mail-Adresse wurde bereits verwendet'
        ],
        'weight' => [
            'min' => 'Bitte gib einen Wert über :min kg ein',
            'max' => 'Bitte gib einen Wert unter :max kg ein.',
        ],
        'height' => [
            'min' => 'Bitte gib einen Wert über :min cm an.',
            'max' => 'Bitte gib einen Wert unter :max cm ein.',
        ],
    ],

    // Special word separator for info pages
    'info_pages' => [
        'text_separator' => ' und '
    ],

    // Page title displayed in across the app
    'page_title' => 'Fragebogen',

    // Various info messages
    'info' => [
        'temporary_saved'     => 'Bestätige deine E-Mail innerhalb von drei Tagen, um deine Antworten zu speichern.',
        'insufficient_fund'   => 'Lade dein Foodpunkte-Konto auf, um deinen Fragebogen zu ändern.',
        'update_confirmation' => 'Möchtest du deinen Fragebogen wirklich bearbeiten?',
        'withdraw_error'      => 'Foodpunkte können nicht abgezogen werden. Bitte versuche es später erneut.',
        'not_saved_error'     => 'Daten konnten nicht gespeichert werden. Bitte versuche es später erneut.',
    ],

    // Button texts
    'buttons' => [
        'edit_for_fp' => 'Für Foodpunkte bearbeiten',
    ]
];

<?php

declare(strict_types=1);

return [
    // Admin Notifications
    'notification'                  => 'Benachrichtigung',
    'notification_link'             => 'Weiterführender Link',
    'notification_title'            => 'Link Button Beschriftung :lang',
    'notification_link_url'         => 'Link URL',
    'notifications'                 => 'Benachrichtigungen',
    'notification_type'             => 'Benachrichtigungstyp',
    'important'                     => 'Wichtig?',
    'dispatch'                      => 'Absenden',
    'dispatch_title'                => 'Benachrichtigungsversand',
    'notification_config'           => 'Hier kannst du noch zusätzliche Einstellungen für den Versand der Benachrichtigung tätigen. Klicke nach Fertigstellung auf Absenden,um die Benachrichtigung abzusenden. ACHTUNG: Nachdem die Benachrichtigung abgeschickt wurde, kannst du sie nicht mehr bearbeiten!',
    'notification_dispatch_options' => [
        'user_groups' => 'Nutzergruppe',
        'course'      => [
            'label_course' => 'Kurs auswählen',
            'label_status' => 'Kurs Status',
        ]
    ],
    'notification_config_empty'       => 'Keine zusätzlichen Einstellungen möglich.',
    'notification_dispatched_success' => 'Auftrag erstellt. Benachrichtigung wird demnächst im Hintergrund verschickt.',
    'notification_report'             => [
        'label' => 'Bericht',
        'modal' => [
            'button'     => 'Siehe Bericht',
            'title'      => 'Benachrichtigungsprotokoll',
            'info'       => 'Info',
            'error'      => 'Fehler',
            'params'     => 'Parameter',
            'user_group' => 'Adressierte Usergruppe',
        ],
        'status' => [
            'success'     => 'Benachrichtigung erfolgreich verschickt',
            'with_errors' => 'Dispatched partially',
            'failed'      => 'Senden der Benachrichtigung fehlgeschlagen',
        ],
    ],
    'notification_info_modal' => [
        'button' => 'Siehe Info',
        'title'  => 'Benachrichtigungsinfo',
    ],
    'no_users_found' => 'No users found for stated criteria.',
];

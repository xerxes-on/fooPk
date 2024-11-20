<?php

declare(strict_types=1);

return [
    'notification'                  => 'Notification',
    'notification_link'             => 'Notification link',
    'notification_title'            => 'Link title :lang',
    'notification_link_url'         => 'Link URL',
    'notifications'                 => 'Notifications',
    'notification_type'             => 'Notification Type',
    'important'                     => 'Is important?',
    'dispatch'                      => 'Dispatch',
    'dispatch_title'                => 'Notification dispatching',
    'notification_config'           => 'Here you can provide extra options for notification dispatching. After finishing click on submit button to dispatch notification. Be aware, after notifications is dispatched, you can not edit it anymore.',
    'notification_dispatch_options' => [
        'user_groups' => 'User group',
        'course'      => [
            'label_course' => 'Select course',
            'label_status' => 'Course status',
        ]
    ],
    'notification_config_empty'       => 'No extra options available now.',
    'notification_dispatched_success' => 'Job created. Notification will be dispatched in background soon.',
    'notification_report'             => [
        'label' => 'Report',
        'modal' => [
            'button'     => 'See report',
            'title'      => 'Notification report',
            'info'       => 'Info',
            'error'      => 'Errors',
            'params'     => 'Parameters',
            'user_group' => 'Dispatched user group',
        ],
        'status' => [
            'success'     => 'Successfully dispatched',
            'with_errors' => 'Dispatched partially',
            'failed'      => 'Dispatch failed',
        ],
    ],
    'notification_info_modal' => [
        'button' => 'See info',
        'title'  => 'Notification info',
    ],
    'no_users_found' => 'No users found for stated criteria.',
];

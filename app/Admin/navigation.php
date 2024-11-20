<?php

use App\Enums\Admin\Permission\PermissionEnum;
use App\Enums\Admin\SectionPagesEnum;
use SleepingOwl\Admin\Navigation\Badge;

$locale = Cookie::get('translatable_lang', config('app.locale'));

AdminNavigation::setFromArray(
    [
        [
            'title'    => trans('common.users', locale: $locale),
            'priority' => 30,
            'icon'     => 'fas fa-users',
            'id'       => SectionPagesEnum::USERS->value,
            'pages'    => [
                'title'    => trans('common.analytics', locale: $locale),
                'badge'    => new Badge('Dev'),
                'url'      => 'admin/clients/analytics',
                'priority' => 39,
            ]
        ],
        [
            'title'       => trans('common.media_library', locale: $locale),
            'priority'    => 40,
            'icon'        => 'far fa-file',
            'accessLogic' => fn() => Auth::user()->hasPermissionTo(PermissionEnum::MEDIA_LIBRARY_MENU->value),
            'pages'       => [
                [
                    'title'       => trans('common.file_manager', locale: $locale),
                    'priority'    => 41,
                    'icon'        => 'far fa-copy',
                    'url'         => 'admin/elfinder',
                    'accessLogic' => fn() => Auth::user()->hasPermissionTo(PermissionEnum::SEE_ALL_MEDIA_LIBRARY->value),
                ],
                [
                    'title'       => trans('common.pictures_video_import', locale: $locale),
                    'priority'    => 43,
                    'badge'       => new Badge('Dev'),
                    'url'         => 'admin/media-library/import',
                    'accessLogic' => fn() => Auth::user()->hasPermissionTo(PermissionEnum::IMPORT_MEDIA_LIBRARY->value),
                ],
                [
                    'title'       => trans('common.media_library', locale: $locale),
                    'priority'    => 44,
                    'badge'       => new Badge('Dev'),
                    'url'         => 'admin/media-library',
                    'accessLogic' => fn() => Auth::user()->hasPermissionTo(PermissionEnum::IMPORT_MEDIA_LIBRARY->value),
                ],
            ]
        ],
        [
            'title'    => trans('common.settings', locale: $locale),
            'priority' => 100,
            'icon'     => 'fas fa-cogs',
            'id'       => SectionPagesEnum::SETTINGS->value,
        ],
    ]
);

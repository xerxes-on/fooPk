<?php

namespace App\Enums\Admin;

/**
 * Enum determining methods of formular creation.
 *
 * @package App\Enums\Admin\Client
 */
enum SectionPagesEnum: string
{
    case USERS    = 'users';
    case SETTINGS = 'settings';
}

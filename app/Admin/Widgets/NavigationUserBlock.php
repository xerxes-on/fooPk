<?php

declare(strict_types=1);

namespace App\Admin\Widgets;

use AdminTemplate;
use SleepingOwl\Admin\Widgets\Widget;

/**
 * Widget NavigationUserBlock
 *
 * @package App\Http\Widgets
 */
final class NavigationUserBlock extends Widget
{
    public function toHtml(): string
    {
        return view('auth.partials.navbar', ['user' => auth()->user()])->render();
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return AdminTemplate::getViewPath('_partials.header');
    }

    /**
     * @return string
     */
    public function block(): string
    {
        return 'navbar.right';
    }
}

<?php

namespace App\View;

use Illuminate\View\View;

/**
 * Share user with designated views.
 * TODO: Probably should consider some excluded routes as well
 * @package App\View
 */
final class UserComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $viewName = strtolower($view->getName());
        // Prevent email templates from getting user data
        if (str_starts_with('emails.', $viewName) || str_contains($viewName, 'mail')) {
            return;
        }
        $view->with('user', auth()->user());
    }
}

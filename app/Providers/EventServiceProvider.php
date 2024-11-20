<?php

namespace App\Providers;

use App\Events;
use App\Listeners;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class                      => [SendEmailVerificationNotification::class],
        Events\RecipeProcessed::class          => [Listeners\ClearUserRecipeCache::class],
        Events\AdminActionsTaken::class        => [Listeners\WipeCache::class],
        Events\UserProfileChanged::class       => [Listeners\ClearUserCache::class],
        Events\UserRecipeUpdated::class        => [Listeners\CheckUserRecipeCount::class],
        Events\UserQuestionnaireChanged::class => [Listeners\ClearUserQuestionnaireCache::class],
    ];
}

<?php

namespace App\Providers;

use App\Models\ClientNote;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     * 'App\Models\Model' => 'App\Policies\ModelPolicy'
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('update-client-note', static fn(User $user, ClientNote $note) => $user->id === $note->author_id);
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(
            function (IncomingEntry $entry) {
                if ($this->app->environment(['local', 'staging'])) {
                    return true;
                }

                if (config('telescope.log_all')) {
                    return true;
                }
                return $entry->isSlowQuery() ||
                    ($entry->type === EntryType::REQUEST && ($entry->content['response_status'] ?? 200) >= 400) ||
                    $entry->isException() ||
                    $entry->isFailedJob() ||
                    $entry->isScheduledTask() ||
                    $entry->hasMonitoredTag() ||
                    $entry->type === EntryType::LOG ||
                    $entry->type === EntryType::MAIL;
            }
        );
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails()
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders(
            [
                'cookie',
                'x-csrf-token',
                'x-xsrf-token',
            ]
        );
    }

    protected function authorization()
    {
        $this->gate();

        Telescope::auth(function ($request) {
            $user = $request->user();
            if (empty($user)) {
                Auth::shouldUse('admin');
                $user = $request->user();
            }

            return app()->environment('local') || Gate::check('viewTelescope', [$user]);
        });
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define(
            'viewTelescope',
            function ($user) {
                return in_array($user->email, [
                    'itechuser@foodpunk.de',
                    'master@foodpunk.de',
                ]);
            }
        );
    }
}

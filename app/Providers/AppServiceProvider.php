<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\View\GlobalVariableComposer;
use App\View\UserComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191); // Required by laravel-permission

        // Override default Sanctum PersonalAccessToken model for custom prunable capabilities.
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        if (!$this->app->isLocal()) {
            \URL::forceScheme('https');
        }

        if ((config('telescope.enabled') && config('telescope.manually_enabled')) || !$this->app->isProduction()) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        Paginator::defaultView('pagination::bootstrap-4');
        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            /**
             * @var \Illuminate\Database\Query\Builder $this
             * From EnumeratesValues trait
             */
            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path'     => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });

        /**
         * Preventing lazy loading for all models. Allow to find all places where lazy loading is used.
         */
        Model::preventLazyLoading(config('app.allow_lazy_loading'));

        View::composer('*', UserComposer::class);
        View::composer(GlobalVariableComposer::PUBLIC_VIEWS, GlobalVariableComposer::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\CheckAmountOfUserRecipes;
use App\Jobs\NotifyUserToFillQuestionnaireJob;
use App\Models\PersonalAccessToken;
use App\Models\QuestionnaireTemporary;
use App\Models\RecipeDistributionToUser;
use App\Services\AdminStorage;
use App\Models\CustomRecipe;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\PushNotification\Models\UserDevice;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $env = $this->app->environment();

        /**
         * Clean up old models data.
         * @note for model pruning we MUST explicitly specify the model classes.
         * This is due to we have modules, and it differs from standard Laravel model pruning command behavior.
         */
        $schedule->command('model:prune', [
            '--model' => [
                PersonalAccessToken::class,
                QuestionnaireTemporary::class,
                RecipeDistributionToUser::class,
                AdminStorage::class,
                UserDevice::class,
                CustomRecipe::class,
            ],
        ])->dailyAt('00:10')->withoutOverlapping();
        $schedule->command('app:prune-password-reset-table')->dailyAt('00:15')->withoutOverlapping();

        // Delete old users.
        // TODO:: temporary disabled WEB-472
        //$schedule->command('users:clean')->monthlyOn(10, '01:00')->withoutOverlapping();

        // Delete ownerless data.
        // TODO:: temporary disabled WEB-472
        //$schedule->command('users:clean-ownerless-data')->monthlyOn(15, '01:00')->withoutOverlapping();

        $schedule->command('user_deactivation_check')->dailyAt('02:30')->withoutOverlapping();

        $schedule->job(new CheckAmountOfUserRecipes())->dailyAt('05:00');

        // Specific environment commands.

        if ('production' === $env) {
            $schedule->command('recipe:preliminary-recalculation')->dailyAt('04:15')->withoutOverlapping();
            $schedule->job(NotifyUserToFillQuestionnaireJob::class)->dailyAt('00:30')->withoutOverlapping();
            $schedule->command('recipe:distribution')->monthlyOn(1, '01:00')->withoutOverlapping();
            // sport challenge checker
            // deprecated, TBR2024
//            $schedule->command('checker:challenges-sport')->everyThreeHours()->withoutOverlapping();

            // weekly foodpoints distribution, runs every day and distribute to allowed users
            $schedule->command('foodpoints:monthly-distribution')->dailyAt('10:30')->withoutOverlapping();
        } else {
            // for non-production distribution works only for test-users
            $schedule->command('foodpoints:monthly-distribution --force')->dailyAt('10:30')->withoutOverlapping();
        }

        if ((config('telescope.enabled') && config('telescope.manually_enabled')) || 'production' !== $env) {
            $schedule->command('telescope:prune --hours=36')->dailyAt('04:30');
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}

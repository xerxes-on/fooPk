<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Events\AdminActionsTaken;
use App\Jobs\PreliminaryCalculation as JobPreliminaryCalculation;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Console\Command;
use Modules\Internal\Models\AdminStorage;

/**
 * Calculate all suitable recipes to users
 *
 * @package App\Console\Commands
 */
final class PreliminaryCalculation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipe:preliminary-recalculation
                            {user? : The Email of the user}
                            {--all-recipes : recalculate all exists recipes in the system}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate all suitable recipes to users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Calculate of all suitable recipes started...');
        $this->info('');

        # get Email argument
        $userEmail             = $this->argument('user');
        $recalculateAllRecipes = (bool)$this->option('all-recipes');

        # the number Of Recipes
        $numberOfRecipes = Recipe::isActive()->count();

        $queue = 'high';

        # get user data
        if (is_null($userEmail)) {
            // last recipe edit date //recipes
            // TODO::potential fix for recipes which have been edited
            // get last edit recipe date and compare with calculation date
            $userData = User::active()
                ->whereNotNull('dietdata')
                ->where(
                    function ($query) use ($numberOfRecipes) {
                        $query->doesnthave('preliminaryCalc')
                            ->orWhereHas(
                                'preliminaryCalc',
                                function ($q) use ($numberOfRecipes) {
                                    $q->whereNull('valid')
                                        ->orWhere('counted', '!=', $numberOfRecipes);
                                }
                            );
                    }
                )
                ->orderBy('id', 'DESC')
                ->get();
            $queue = 'low';
        } else {
            $userData = User::whereEmail($userEmail)
                ->whereNotNull('dietdata')
                ->get();
        }

        $errorUser = 0;
        foreach ($userData as $_user) {
            /**@var User $_user */
            $questionnaireExists = $_user->isQuestionnaireExist();
            if (!$questionnaireExists || ($questionnaireExists && !$_user->questionnaire_approved)
                //
                //                 || $_user->preliminaryCalc()->count() > 0
                //                 || is_null($_user->preliminaryCalc()->first()->valid)
                //                  ||
                //                 ($_user->preliminaryCalc()->first()->counted == $numberOfRecipes)
            ) {
                $errorUser++;
                continue;
            }

            $jobStartHash = AdminStorage::generatePreliminaryJobHash($_user->getKey());
            JobPreliminaryCalculation::dispatch($_user, $recalculateAllRecipes, $jobStartHash)
                ->onQueue($queue)
                ->delay(now()->addSeconds(5));
        }

        $this->info("Count user: \t" . $userData->count());
        $this->info("Error user: \t" . $errorUser);

        AdminActionsTaken::dispatch();

        return self::SUCCESS;
    }
}

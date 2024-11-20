<?php

namespace App\Console\Commands;

use App\Events\AdminActionsTaken;
use App\Jobs\AddingNewRecipes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;

/**
 * Adding New Recipes Monthly
 *
 * @package App\Console\Commands
 */
final class AddingNewRecipesMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipe:adding:monthly
                            {user? : The Email of the user}
                            {--debug=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adding New Recipes Monthly';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $startDate = Carbon::now();

        $this->info('');
        $this->info('Adding New Recipes Monthly started...');
        $this->info('');

        # get Email argument
        $userEmail = $this->argument('user');

        # get debug options
        $debug = !is_null($this->option('debug')) && $this->option('debug') === 'true';

        # get user data
        $userData = is_null($userEmail) ?
            User::active()
                ->orderBy('id')
                ->get() :
            User::whereEmail($userEmail)
                ->active()
                ->orderBy('id')
                ->get();


        $errorUser = 0;
        foreach ($userData as $user) {
            /** @var User $user */
            if (empty($user->subscription) || !$user->isQuestionnaireExist()) {
                $this->info('----------');
                $this->info('User email: ' . $user->email);
                $this->error('User does not have subscription or formular - skipping!');
                $this->info(' ');

                $errorUser++;
                continue;
            }

            SyncUserExcludedIngredientsJob::dispatchSync($user);
            AddingNewRecipes::dispatch($user, 'monthly', $debug)
                ->onQueue('low')
                ->delay(now()->addSeconds(5));
        }

        //        if (!$debug) {
        //            $configRecord                 = RecipeDistribution::where('is_distributed', false)->orderBy('id')->first();
        //            $configRecord->is_distributed = true;
        //            $configRecord->save();
        //        }

        $this->info("Count user: \t" . $userData->count());
        $this->info("Error user: \t" . $errorUser);
        AdminActionsTaken::dispatch();

        return self::SUCCESS;
    }
}

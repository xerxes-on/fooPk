<?php

namespace App\Console\Commands;

use App\Events\AdminActionsTaken;
use App\Helpers\Calculation;
use App\Models\User;
use Illuminate\Console\Command;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;

/**
 * Recalculation recipes to users
 *
 * @package App\Console\Commands
 */
final class RecipeRecalculation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipe:recalculation
                            {user? : The Email of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculation recipes to users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        # get Email argument
        $userEmail = $this->argument('user');

        # get user data
        $userData = is_null($userEmail) ? User::active() : User::ofEmail($userEmail);
        $userData = $userData->get();

        foreach ($userData as $_user) {
            $this->info('----------');
            $this->info('User email: ' . $_user->email);

            # check formular answer
            if ($_user->isQuestionnaireExist()) {
                # get recipe Ids
                $recipeIds = $_user->allRecipes()->orderBy('recipes.id')->pluck('recipes.id')->toArray();

                $this->output->progressStart(count($recipeIds));

                SyncUserExcludedIngredientsJob::dispatchSync($_user);
                # recipe from user create
                foreach ($recipeIds as $index => $recipeId) {
                    //$this->info($recipeId);


                    Calculation::_calcRecipe2user($_user, [$recipeId]);

                    $this->output->progressAdvance();
                }

                $this->output->progressFinish();
                //Calculation::_calcRecipe2user($_user, $recipeIds);
            } else {
                $this->info('First you need to fill and save formular.');
                $this->info('');
            }

            $this->info('Recipes by dates generated Successfully!');
            $this->info('');
        }

        AdminActionsTaken::dispatch();

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Events\AdminActionsTaken;
use App\Models\{Ingestion, User, UserRecipe};
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Distribute recipes to users until the end of next month
 * TODO: optimization required
 * @package App\Console\Commands
 */
final class RecipeDistribution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipe:distribution
                            {user? : The Email of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Distribute recipes to users until the end of next month';

    /**
     * Execute the console command.
     *
     * TODO: method has 166 lines of code. Split into smaller methods.
     * TODO: method has a Cyclomatic Complexity of 16. Simplify this method to reduce its complexity.
     * TODO: method has an NPath complexity of 3460. Simplify this method to reduce its complexity.
     * TODO: split into chucking methods to run service which will perform the distribution
     * TODO: provide better typehints
     */
    public function handle(): int
    {
        # get Email argument
        $userEmail = $this->argument('user');

        # get user data
        $userData = is_null($userEmail) ? User::active() : User::ofEmail($userEmail);
        $userData = $userData->get();

        $nextMonthPlus2Weeks = Carbon::now()->startOfMonth()->addMonth();//->addWeeks(2);

        if ($nextMonthPlus2Weeks->format('D') != 'Mon') {
            $nextMonthPlus2Weeks = $nextMonthPlus2Weeks->copy()->startOfMonth()->modify('first Monday');
        }
        $nextMonthPlus2Weeks->addWeeks(2);

        # get ingestions
        $ingestions = Ingestion::active()->get();

        $this->output->progressStart(count($userData));
        foreach ($userData as $_user) {
            $this->output->progressAdvance();

            $subscriptionData = $_user->getLatestSubscription();
            if (
                empty($_user->subscription)
                ||
                !$_user->isQuestionnaireExist()
            ) {
                continue;
            }

            $excluded_recipes_ids_by_user_exclusion = [];
            if (!empty($_user->excluded_recipes)) {
                $excluded_recipes_ids_by_user_exclusion = $_user->excluded_recipes->toArray();
            }

            # recipe Id collections
            $recipeDate = array();
            //            TODO: high resource intake. refactor required
            foreach ($ingestions as $ingestion) {
                /*$recipeIds = \App\Models\UserRecipeCalculated::where('user_id', $_user->id)
                    ->whereNotNull('recipe_id')
                    ->where('invalid', 0)
                    ->where('ingestion_id', $ingestion->id)
                    ->pluck('recipe_id')
                    ->toArray();*/

                $recipeIds = $_user
                    ->allRecipes()
                    ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
                    ->leftJoin('ingestions', 'user_recipe_calculated.ingestion_id', '=', 'ingestions.id')
                    ->select(
                        'recipes.*',
                        'user_recipe_calculated.ingestion_id AS calc_ingestion_id',
                        'user_recipe_calculated.recipe_data AS calc_recipe_data',
                        'user_recipe_calculated.invalid AS calc_invalid',
                        'user_recipe_calculated.created_at AS calc_created_at',
                        'ingestions.key AS meal_time'
                    )
                    ->where('user_recipe_calculated.user_id', $_user->id)
                    ->where('user_recipe_calculated.ingestion_id', $ingestion->id)
                    ->where('user_recipe_calculated.invalid', '=', 0)
                    ->whereNotIn('recipes.id', $excluded_recipes_ids_by_user_exclusion)
                    ->groupBy('recipes.id')
                    ->pluck('recipes.id')
                    ->toArray();

                if (empty($recipeIds)) {
                    continue;
                }

                $recipeDate[$ingestion->id] = $recipeIds;
            }

            if (empty($recipeDate)) {
                continue;
            }

            # get last date
            $_lastDate = UserRecipe::whereUserId($_user->id)
//								   ->whereChallengeId($_user->subscription->id)
                ->latest('meal_date')
                ->pluck('meal_date')
                ->first();

            // TODO: parse can throw Exception
            $_lastDate = Carbon::parse($_lastDate);
            $startDate = $_lastDate->copy()->addDay();
            //			$endDate   = now()->addMonth();
            $endDate = clone $nextMonthPlus2Weeks;


            if (!is_null($subscriptionData->ends_at) && ($subscriptionData->ends_at < $endDate)) {
                $endDate = Carbon::parse($subscriptionData->ends_at); // TODO: parse can throw Exception
            }

            # check different day, if last Date in future, TODO:: @NickMost ask foodpunk about case
            if ($endDate->diffInDays($_lastDate, false) >= 0) {
                continue;
            }

            # ==================
            # START Distribution
            # ==================

            //			$this->info('----------');
            //			$this->info('User email: '.$_user->email);

            // TODO: should be optimized
            while ($startDate < $endDate) {
                # recipe from user create
                $preparedData = array(); // TODO: can be set outside the loop to gather all and insert at once in the end

                foreach ($ingestions as $ingestion) {
                    if (!key_exists($ingestion->id, $recipeDate)) {
                        continue;
                    }

                    # randomize recipe ID by ingestion
                    $randomRecipeId = $recipeDate[$ingestion->id][array_rand($recipeDate[$ingestion->id])];

                    # get recipe
                    //$_recipe = \App\Models\Recipe::find($randomRecipeId);

                    $preparedData[] = [
                        'user_id'            => $_user->id,
                        'recipe_id'          => $randomRecipeId,
                        'custom_recipe_id'   => null,
                        'original_recipe_id' => $randomRecipeId,
                        // TODO:: review challenge_id @NickMost
                        //						'challenge_id'       => $userSubscription->id,
                        'meal_date'    => $startDate->format('Y-m-d 00:00:00'),
                        'meal_time'    => $ingestion->key,
                        'ingestion_id' => $ingestion->id,
                    ];
                }

                /**
                 * TODO: Optimization required!!!
                 * here we an insert operation wil will perform a single insert per each iteration.
                 * To make it faster we need to perform single insert with multiple columns as it will be much faster
                 * @link https://dev.mysql.com/doc/refman/8.0/en/insert-optimization.html
                 * @note Estimations
                 * 2 rows at a time: 3.5 - 3.5 seconds
                 * 5 rows at a time: 2.2 - 2.2 seconds
                 * 10 rows at a time: 1.7 - 1.7 seconds
                 * 50 rows at a time: 1.17 - 1.18 seconds
                 * 100 rows at a time: 1.1 - 1.4 seconds
                 * 500 rows at a time: 1.1 - 1.2 seconds
                 * 1000 rows at a time: 1.17 - 1.17 seconds
                 */
                UserRecipe::insert($preparedData);

                $startDate->addDay();
            }

            //			$this->info('Recipes by dates generated Successfully!');
            //			$this->info('');
        }
        $this->output->progressFinish();

        AdminActionsTaken::dispatch();

        return self::SUCCESS;
    }
}

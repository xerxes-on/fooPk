<?php

namespace Modules\Internal\Console\Commands;

use App\Helpers\Calculation;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Validate recipe for user with debug data
 *
 * @internal
 *
 * @package App\Console\Commands\Internal
 */
final class ValidateRecipeForUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validate_recipe_for_user {user : The Email/ID of the user} {recipe : recipe id} {--replace-from-user-scope} {--replace-outside-user-scope}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate recipe for user by user email and recipe id';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $email = trim(strtolower($this->argument('user')));
        if ($email == intval($email)) {
            $_user = User::find($email);
        } else {
            $_user = User::whereEmail($email)->first();
        }

        $recipeId = trim(strtolower($this->argument('recipe')));


        $replaceFromUserScope    = boolval($this->option('replace-from-user-scope'));
        $replaceOutsideUserScope = boolval($this->option('replace-outside-user-scope'));

        $recipeValid = true;


        $_recipe = Recipe::find($recipeId);

        # check valid recipe data
        $validRecipeData = Calculation::checkUserValidRecipe($recipeId, $_user);
        if ($validRecipeData['valid'] !== true) {
            $recipeValid = false;
        }
        dump('$validRecipeData', $validRecipeData);

        $validIngestions = [];
        // to add getting all recipe + general ingestions from database (+ inactive) remove all inactive from database
        foreach ($_recipe->ingestions as $ingestion) {
            # check active meal time (ingestion)
            if (empty($ingestion->active)) {
                continue;
            }
            dump('--------------------------------------');

            # check valid by KCal/KH range
            $validByKCalKHData = Calculation::validRecipeKcalKH($_recipe, $ingestion, $_user->dietdata);

            dump('$ingestion', $ingestion->key);
            dump('$validByKCalKHData', $validByKCalKHData);

            # calc recipe
            $recipeData = Calculation::calcRecipe($_recipe, $ingestion, $_user->dietdata);

            # recipeData optimization
            $recipeData = Calculation::calcRecipeOptimization($_recipe, $recipeData, $validRecipeData, $validByKCalKHData);

            if (empty($recipeData['errors'])) {
                $validIngestions[] = $ingestion;
            }

            dump('$recipeData - errors', $recipeData['errors']);
            dump('$recipeData - notices', $recipeData['notices']);
            dump('--------------------------------------');
        }
        if (empty($validIngestions)) {
            //
            $recipeValid = false;
        }

        if (!$recipeValid && ($replaceFromUserScope || $replaceOutsideUserScope)) {
            if ($replaceFromUserScope) {
                //
                // TODO:: @NickMost finish it
            }

            if ($replaceOutsideUserScope) {
                //
                // TODO:: @NickMost finish it
            }
        }

        return Command::SUCCESS;
    }
}

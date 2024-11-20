<?php

namespace Modules\Internal\Console\Commands;

use App\Jobs\RecalculateForAllUsers;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 * @package App\Console\Commands\Internal
 */
final class relaculateForAllUsersRecipesWithIngredient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internal:recalculate-for-all-users-recipes-with-ingredient {ingredientId? : Ingredient ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';


    protected int $ingredientId;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $ingredientId = $this->ingredientId = $this->argument('ingredientId');
        if (empty($ingredientId)) {
            return;
        }

        $recipes = Recipe::whereHas('ingredients', static function ($q) use ($ingredientId) {
            $q->where('ingredient_id', $ingredientId);
        })->orWhereHas('variableIngredients', function ($q) use ($ingredientId) {
            $q->where('ingredient_id', $ingredientId);
        })->distinct()->orderBy('id')->get(['id'])->pluck('id')->toArray();

        if (empty($recipes)) {
            return;
        }

        $this->info('Recipes ID: ' . implode(', ', $recipes));
        $this->info('Total amount of recipes: ' . count($recipes));
        $this->info('===============================================');

        foreach ($recipes as $recipeId) {
            $userIds = DB::table('user_recipe')
                ->where('recipe_id', $recipeId)
                ->orderBy('user_id')
                ->pluck('user_id')
                ->toArray();
            $users = User::active()
                ->whereIn('users.id', $userIds)
                ->whereHas('questionnaire')
                ->whereHas('activeSubscriptions')
                ->orderBy('users.id', 'DESC')
                ->pluck('id')
                ->toArray();

            if (!empty($users)) {
                $this->info('Recipes ID: ' . $recipeId . ' Users: ' . implode(', ', $users));
                $this->info('Total amount of active users for recipe: ' . count($users));
                RecalculateForAllUsers::dispatch($users, $recipeId);
                $this->info('===============================================');
            }
        }
    }
}

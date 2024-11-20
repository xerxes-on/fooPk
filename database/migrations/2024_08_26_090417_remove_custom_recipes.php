<?php

declare(strict_types=1);

use App\Enums\Recipe\RecipeTypeEnum;
use App\Exports\DeletedCustomRecipes;
use App\Mail\ExportUpdateNotifier;
use App\Models\CustomRecipe;
use App\Models\UserRecipe;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Excel;
use Modules\ShoppingList\Models\ShoppingListRecipe;
use Modules\ShoppingList\Services\ShoppingListAssistanceService;

return new class () extends Migration {
    private Carbon $currentWeekStart;
    private string $exportFilePath = '/exports/deleted_custom_recipes.csv';

    public function __construct()
    {
        $this->currentWeekStart = now()->startOfWeek();
    }

    public function up(): void
    {
        // 0. we need to remove all ingredients that are not connected to any custom recipe and make it possible to set foreign key
        $this->clearOwnerlessRecords();
        if (!Schema::hasColumn('custom_recipes', 'error')) {
            return;
        }
        // 1. we need to add foreign delete cascade  to ensure ingredients will be cleared out as well
        Schema::table('ingredients_to_custom_recipes', static function (Blueprint $table) {
            $table->foreign('custom_recipe_id')->references('id')->on('custom_recipes')->cascadeOnDelete();
        });

        // 2. we need to export potentially removing data
        $this->exportData();

        // 3. we need to remove all custom recipes that were created before the current week
        $this->clearOldShoppingLists();

        try {
            \DB::transaction(function () {
                $this->clearOldMealPlan();
                $this->clearOldCustomRecipes();
            }, config('database.transaction_attempts'));
        } catch (\Throwable $e) {
            logError($e, ['message' => 'Error while clearing old meal plan and custom recipes']);
        }

        // 4. starting the current week we need to REPLACE all custom recipes with random ones
        $this->replaceCustomRecipes();

        Schema::table('recipes_to_users', static function (Blueprint $table) {
            $table->dropColumn(['challenge_id']);
            $table->integer('original_recipe_id')->nullable(false)->change();
            $table->string('meal_time', 20)->nullable(false)->change();
        });

        Schema::table('custom_recipes', static function (Blueprint $table) {
            $table->dropColumn(['challenge_id', 'error']);
            $table->unsignedInteger('recipe_id')->nullable(false)->change();
        });

        $this->notifyAdmins();
    }

    private function clearOwnerlessRecords(): void
    {
        DB::table('ingredients_to_custom_recipes')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('custom_recipes')
                    ->whereColumn('custom_recipes.id', 'ingredients_to_custom_recipes.custom_recipe_id');
            })
            ->delete();
    }

    private function exportData(): void
    {
        (new DeletedCustomRecipes(
            CustomRecipe::with(['ingredients' => fn(Relation $q) => $q->withOnly('translations')->select(['ingredients.id', 'ingredients.proteins', 'ingredients.fats', 'ingredients.carbohydrates', 'ingredients.calories'])])
                ->whereNull('recipe_id')
                ->get(
                    ['custom_recipes.id', 'custom_recipes.title']
                )
        ))->store(
            filePath: $this->exportFilePath,
            writerType: Excel::CSV
        );
    }

    private function notifyAdmins(): void
    {
        Mail::later(now()->addMinutes(5), new ExportUpdateNotifier($this->exportFilePath), 'emails');
    }

    private function clearOldShoppingLists(): void
    {
        ShoppingListRecipe::with(['shoppingList.user'])
            ->where('recipe_type', RecipeTypeEnum::CUSTOM->value)
            ->whereDate('meal_day', '<', $this->currentWeekStart)
            ->chunk(300, function (Collection $shoppingListRecipes) {
                $shoppingListRecipes->each(static function (ShoppingListRecipe $shoppingListRecipe) {
                    app(ShoppingListAssistanceService::class)
                        ->maybeDeleteRecipe(
                            $shoppingListRecipe->shoppingList->user,
                            $shoppingListRecipe->recipe_id,
                            $shoppingListRecipe->recipe_type->value,
                            $shoppingListRecipe->meal_day,
                            $shoppingListRecipe->mealtime->value
                        );
                });
            });
    }

    private function clearOldMealPlan(): void
    {
        UserRecipe::whereNull('recipe_id')
            ->whereNotNull('custom_recipe_id')
            ->whereNull('flexmeal_id')
            ->whereDate('meal_date', '<', $this->currentWeekStart)
            ->delete();
    }

    private function clearOldCustomRecipes(): void
    {
        CustomRecipe::whereNull('recipe_id')->where('created_at', '<', $this->currentWeekStart)->delete();
    }

    private function replaceCustomRecipes(): void
    {
        UserRecipe::with('user.excludedRecipes:id')
            ->whereNull('recipe_id')
            ->whereNotNull('custom_recipe_id')
            ->whereNull('flexmeal_id')
            ->whereDate('meal_date', '>=', $this->currentWeekStart)
            ->lazyById()
            ->each(function (UserRecipe $recipe) {
                // todo: logerror with user id recipe id and some extra details
                try {
                    \DB::transaction(function () use ($recipe) {
                        $this->attemptToReplaceUserRecipe($recipe);
                        $recipe->delete();
                    }, config('database.transaction_attempts'));
                } catch (\Throwable $e) {
                    logError(
                        $e,
                        [
                            'message'   => 'Error while replacing custom recipe',
                            'recipe_id' => $recipe->id,
                            'user_id'   => $recipe->user_id,
                            'meal_date' => $recipe->meal_date,
                            'meal_time' => $recipe->meal_time
                        ]
                    );
                }

            });
    }

    private function attemptToReplaceUserRecipe(UserRecipe $recipe): void
    {
        try {
            app(ShoppingListAssistanceService::class)->maybeDeleteRecipe(
                $recipe->user,
                $recipe->custom_recipe_id,
                RecipeTypeEnum::CUSTOM->value,
                $recipe->meal_date,
                $recipe->meal_time
            );
            $recipeId = $recipe->replaceWithRandom($recipe->user->excludedRecipes->pluck('id')->toArray());
            app(ShoppingListAssistanceService::class)->maybeAddRecipe(
                $recipe->user,
                $recipeId,
                RecipeTypeEnum::ORIGINAL->value,
                $recipe->meal_date,
                $recipe->meal_time
            );
        } catch (\Throwable) {
            // nothing to do
        }
    }
};

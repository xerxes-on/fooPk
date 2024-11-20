<?php

declare(strict_types=1);

namespace App\Admin\Services;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Events\RecipeProcessed;
use App\Helpers\Calculation;
use App\Listeners\ClearUserRecipeCache;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserRecipe;
use App\Models\UserRecipeCalculated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\ShoppingList\Models\ShoppingList;
use Modules\ShoppingList\Services\ShoppingListRecipesService;
use Throwable;

/**
 * Service for deleting user recipes.
 *
 * @package App\Admin\Services
 */
class RecipeDeletionService
{
    public function __construct(private readonly ShoppingListRecipesService $service)
    {
    }

    /**
     * Delete recipe by user and replace it with another random recipe.
     *
     * @throws Throwable
     */
    public function destroy(User $user, Recipe $recipe): string
    {
        $message = '';
        DB::transaction(
            function () use ($user, $recipe, &$message) {
                $message = $this->handleDestroyAndReplace($user, $recipe);
            },
            config('database.transaction_attempts')
        );

        $this->cleanUserRecipeCache($user->id);

        return $message;
    }

    /**
     * Delete bulk recipes and replace them with another random recipe.
     */
    public function destroyBulk(User $user, Collection $recipeCollection): array
    {
        $results = [];
        foreach ($recipeCollection as $recipe) {
            try {
                DB::transaction(
                    function () use ($user, $recipe, &$results) {
                        $results[] = [
                            'status'  => 'success',
                            'message' => $this->handleDestroyAndReplace($user, $recipe)
                        ];
                    },
                    config('database.transaction_attempts')
                );
            } catch (Throwable $e) {
                logError($e, ['RecipeDeletionService::destroyBulk']);
                $results[] = [
                    'status'  => 'error',
                    'message' => "Unable to delete recipe #{$recipe->id}. Please try again later."
                ];
            }
        }

        $this->cleanUserRecipeCache($user->id);

        return $results;
    }

    /**
     * Delete all recipes user recipes.
     *
     * @throws Throwable
     */
    public function destroyAll(int $userId): void
    {
        DB::transaction(
            static function () use ($userId) {
                // Wipe shopping list as well as no recipes are left to see
                ShoppingList::whereUserId($userId)->delete();
                DB::table('user_recipe')->where('user_id', $userId)->delete();
                UserRecipe::whereUserId($userId)->delete();
                UserRecipeCalculated::where('user_id', $userId)
                    ->where(
                        static fn(Builder $q) => $q->whereNotNull('custom_recipe_id')->orWhere('invalid', 1)
                    )
                    ->delete();
            },
            config('database.transaction_attempts')
        );

        RecipeProcessed::dispatch();
    }

    /**
     * Process recipe deletion and replacement.
     */
    private function handleDestroyAndReplace(User $user, Recipe $recipe): string
    {
        // Attempt to delete recipe from user's shopping list. Must be done before recipe replacement.
        $existedInShoppingList = false;
        $listMessage           = '';
        try {
            $this->service->deleteRecipe(
                $user,
                $recipe->id,
                RecipeTypeEnum::tryFromClass(get_class($recipe))->value
            );
            $listMessage           = "User Shopping list: Recipe #$recipe->id has been deleted";
            $existedInShoppingList = true;
        } catch (Throwable) {
            // Do nothing
        }

        // Replacing deleted recipe with another one
        $message = Calculation::replaceRecipesInUserFeed([$recipe->id], $user->id, $user);
        $message = Arr::join($message['actions'] ?? ["Recipe #$recipe->id deleted successfully"], '', "\n");

        // Attempt to add recipe from user's shopping list in case it existed and new one was assigned
        if ($existedInShoppingList && isset($message['new_recipe_data'])) {
            try {
                $this->service->addRecipe(
                    $user,
                    $message['new_recipe_data']->recipe_id,
                    RecipeTypeEnum::ORIGINAL->value,
                    $message['new_recipe_data']->meal_date,
                    MealtimeEnum::tryFromValue($message['new_recipe_data']->meal_time)->value,
                );
                $listMessage = "User Shopping list: Recipe #$recipe->id has been replaced with new one #{$message['new_recipe_data']->recipe_id}";
            } catch (Throwable) {
                // Do nothing
            }
        }

        // Deleting traces of old recipe from user's feed
        UserRecipeCalculated::where(
            [
                ['user_id', $user->id],
                ['recipe_id', $recipe->id]
            ]
        )
            ->delete();
        DB::table('user_recipe')
            ->where(
                [
                    ['user_id', $user->id],
                    ['recipe_id', $recipe->id],
                ]
            )
            ->delete();
        UserRecipe::where(
            [
                ['user_id', $user->id],
                ['recipe_id', $recipe->id],
            ]
        )
            ->delete();

        // Adding message about shopping list to response
        if ('' !== $listMessage) {
            $message .= '.<br>' . $listMessage;
        }

        return $message;
    }

    /**
     * Clean user recipe cache.
     */
    private function cleanUserRecipeCache(int $userId): void
    {
        // Clear user recipe cache
        $event = new ClearUserRecipeCache();
        $event->setUserId($userId);
        $event->handle();
    }
}

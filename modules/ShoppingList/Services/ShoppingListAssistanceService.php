<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Services;

use App\Exceptions\PublicException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Helper service to add and remove recipes from the shopping list in case they were already there.
 *
 * @package App\Services\ShoppingList
 */
final class ShoppingListAssistanceService
{
    private bool $existedPreviously = false;

    public function __construct(private readonly ShoppingListRecipesService $service)
    {
    }

    public function maybeDeleteRecipe(User $user, int $recipeId, int $recipeType, ?string $mealDate = null, ?int $mealtime = null): void
    {
        try {
            $this->service->deleteRecipe($user, $recipeId, $recipeType, $mealDate, $mealtime);
            $this->existedPreviously = true;
        } catch (ModelNotFoundException|PublicException) {
            // TODO: maybe show some info later?
        } catch (\InvalidArgumentException $e) {
            logError($e);
        }
    }

    public function maybeAddRecipe(User $user, int $recipeId, int $recipeType, string $mealDay, int $mealTime, int $portions = 1): void
    {
        if (!$this->existedPreviously) {
            return;
        }
        try {
            $this->service->addRecipe($user, $recipeId, $recipeType, $mealDay, $mealTime, $portions);
        } catch (PublicException|\InvalidArgumentException) {
            // TODO: maybe show some info later?
        } catch (\Throwable $e) {
            logError($e);
        }
    }
}

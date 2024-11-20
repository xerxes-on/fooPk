<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recipes;

use App\Events\RecipeProcessed;
use App\Exceptions\PublicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\SkipRecipeRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\ShoppingList\Services\ShoppingListRecipesService;

/**
 * Recipes skip controller
 *
 * @package App\Http\Controllers\Recipes
 */
class RecipeSkipController extends Controller
{
    /**
     * Skip user meal or restore it back.
     *
     * @route POST /user/recipes/eatout
     */
    public function eatOutRecipe(SkipRecipeRequest $request, ShoppingListRecipesService $shoppingListService): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $user->meals()
            ->where([
//				['challenge_id', $user->subscription?->id],
                ['ingestion_id', $request->ingestion->id],
            ])
            ->whereDate('meal_date', $request->date->format('Y-m-d'))
            ->update(['eat_out' => $request->isEatOut]);

        // Attempt to delete old recipe from shopping list in case user tries to skip meal
        if ($request->isEatOut) {
            try {
                $shoppingListService->deleteRecipe(
                    $user,
                    $request->recipe,
                    $request->recipeType,
                    $request->date->format('Y-m-d')
                );
            } catch (ModelNotFoundException|PublicException) {
            } catch (\InvalidArgumentException $e) {
                logError($e);
            }
        }

        RecipeProcessed::dispatch();

        return ($request->expectsJson()) ? response()->json([
            'success' => true,
            'message' => trans('common.success'),
            'errors'  => null
        ]) : back();
    }
}

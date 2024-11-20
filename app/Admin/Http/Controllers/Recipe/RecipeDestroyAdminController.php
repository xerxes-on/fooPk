<?php

namespace App\Admin\Http\Controllers\Recipe;

use App\Admin\Http\Requests\Recipe\DeleteBulkRecipeRequest;
use App\Admin\Services\RecipeDeletionService;
use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Admin controller for deleting recipes.
 *
 * @package App\Admin\Http\Controllers\Recipe
 */
class RecipeDestroyAdminController extends Controller
{
    /**
     * Delete recipe by user and replace it with another random recipe.
     */
    public function deleteRecipeByUser(Request $request, int $recipeId, int $userId, RecipeDeletionService $service): JsonResponse
    {
        try {
            $user = User::whereId($userId)->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['success' => false, 'message' => "User with id #$userId is not found"]);
        }

        try {
            $recipe = Recipe::setEagerLoads([])->findOrFail($recipeId, ['id']);
        } catch (ModelNotFoundException) {
            return response()->json(['success' => false, 'message' => "Recipe with id #$recipeId is not found"]);
        }

        try {
            $message = $service->destroy($user, $recipe);
        } catch (Throwable $e) {
            logError($e, ['RecipeDeletionService::destroy']);
            return response()->json(['success' => false, 'message' => 'Unable to delete recipe. Please try again later.']);
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Delete bulk recipes and replace them with another random recipe.
     */
    public function destroyBulk(DeleteBulkRecipeRequest $request, RecipeDeletionService $service): JsonResponse
    {
        $data     = $service->destroyBulk($request->user, $request->recipeCollection);
        $status   = 'success';
        $messages = '';
        foreach ($data as $result) {
            $messages .= $result['message'] . '<br>';
            if ('error' === $result['status']) {
                $status = 'warning';
            }
        }

        return response()->json(['success' => true, 'status' => $status, 'message' => $messages]);
    }

    /**
     * Delete all recipes by admin.
     */
    public function deleteAllRecipeByAdmin(Request $request, int $userId, RecipeDeletionService $service): JsonResponse
    {
        try {
            $service->destroyAll($userId);
        } catch (Throwable) {
            return response()->json(['success' => false, 'message' => 'Unable to delete recipes. Please try again later.']);
        }

        return response()->json(['success' => true, 'message' => 'Recipes has been deleted.']);
    }
}

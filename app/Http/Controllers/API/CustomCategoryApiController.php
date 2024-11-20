<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Exceptions\CategoryAttached;
use App\Http\Requests\API\{AddCategoryPayload, DetachCategoryPayload, EditCategoryPayload};
use App\Http\Resources\CustomRecipeCategory;
use App\Services\CustomCategories as CustomCategoriesService;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * API controller for custom recipe categories.
 *
 * @package App\Http\Controllers\API
 */
final class CustomCategoryApiController extends APIBase
{
    /**
     * Add custom category to a recipe.
     *
     * @route POST /api/v1/add-recipe-category
     */
    public function addToRecipe(AddCategoryPayload $request, CustomCategoriesService $service): JsonResponse
    {
        $user   = $request->user();
        $recipe = $user->allRecipes()->where('recipe_id', $request->recipe_id)->first();

        if (is_null($recipe)) {
            return $this->sendError(message: "You don't have access to recipe with ID $request->recipe_id.");
        }

        try {
            $category = $service->addToRecipe($user, $request->category_name, $recipe);
        } catch (CategoryAttached $exception) {
            return $this->sendError(
                message: $exception->getMessage(),
                status: ResponseAlias::HTTP_BAD_REQUEST
            );
        }

        return $this->sendResponse(new CustomRecipeCategory($category), trans('common.success'));
    }

    /**
     * Delete custom category.
     *
     * @route DELETE /api/v1/delete-recipe-category/{category_id}
     */
    public function delete(Request $request, int $category_id, CustomCategoriesService $service): JsonResponse
    {
        try {
            $category = $request->user()->customRecipeCategories()->where('id', $category_id)->firstOrFail();
            $service->delete($category);
        } catch (ModelNotFoundException) {
            return $this->sendError(message: "You don't have category with ID $category_id");
        }

        return $this->sendResponse([], "The category \"$category->name\" is removed.");
    }

    /**
     * List recipe categories.
     *
     * @route POST /api/v1/list-recipe-categories
     */
    public function list(Request $request): JsonResponse
    {
        return $this->sendResponse(
            CustomRecipeCategory::collection($request->user()->customRecipeCategories),
            trans('common.success')
        );
    }

    /**
     * Edit custom category.
     *
     * @route  POST /api/v1/edit-recipe-category
     */
    public function edit(EditCategoryPayload $request, CustomCategoriesService $service): JsonResponse
    {
        return $this->sendResponse(
            new CustomRecipeCategory($service->edit($request->category, $request->category_name)),
            trans('common.success')
        );
    }

    /**
     * Detach custom category from a recipe without removing it.
     *
     * @route  POST /api/v1/detach-recipe-category
     */
    public function detach(DetachCategoryPayload $request): JsonResponse
    {
        $user   = $request->user();
        $recipe = $user->allRecipes()->setEagerLoads([])->where('recipe_id', $request->recipe_id)->first(['recipes.id', 'related_recipes']);

        if (is_null($recipe)) {
            return $this->sendError(message: "You don't have access to recipe #$request->recipe_id.");
        }

        $categoryRecordsId = DB::table('custom_recipe_categories')
            ->join(
                'recipes_to_custom_categories',
                'custom_recipe_categories.id',
                '=',
                'recipes_to_custom_categories.category_id'
            )
            ->whereIn('recipe_id', $recipe->related_scope)
            ->where([
                ['user_id', $user->id],
                ['recipes_to_custom_categories.category_id', $request->category_id]
            ])
            ->select(
                [
                    'custom_recipe_categories.id',
                    'custom_recipe_categories.user_id',
                    'recipes_to_custom_categories.id as recipes_to_custom_categories_id',
                    'recipes_to_custom_categories.recipe_id',
                    'recipes_to_custom_categories.category_id'
                ]
            )
            ->pluck('recipes_to_custom_categories_id');

        if (DB::table('recipes_to_custom_categories')->whereIn('id', $categoryRecordsId)->delete()) {
            return $this->sendResponse([], trans('common.success'));
        }

        return $this->sendError(
            message: "Category #$request->category_id wasn't attached to recipe #$recipe->id.",
            status: ResponseAlias::HTTP_BAD_REQUEST,
        );
    }
}

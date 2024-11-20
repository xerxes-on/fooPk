<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers\Recipe;

use App\Helpers\Calculation;
use App\Http\Controllers\Controller;
use App\Models\Ingestion;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request as RequestFacade;

/**
 * Recipe Search Controller
 *
 * @package App\Admin\Http\Controllers\Recipe
 */
class RecipeSearchAdminController extends Controller
{
    /**
     * Search Ingredients by Select2 ajax request.
     */
    public function customSearch(): JsonResponse
    {
        $recipeToExclude = RequestFacade::route('excludedId', 0);
        $searchVal       = RequestFacade::instance()->q;

        if (is_null($searchVal)) {
            return new JsonResponse([]);
        }

        // If condition is true we suppose that user tries to find recipes by title
        $query = (int)$searchVal === 0 ?
            Recipe::whereTranslationLike('title', "%$searchVal%") :
            Recipe::where('id', 'like', "%$searchVal%");

        // Exclude edited recipe from search.
        if (0 !== $recipeToExclude) {
            $query->where('id', '!=', $recipeToExclude);
        }

        // Format response for select2
        return new JsonResponse(
            $query->get()
                ->map(
                    fn(Recipe $item) => [
                        'tag_name'    => "[$item->id] $item->title",
                        'id'          => $item->id,
                        'custom_name' => null,
                    ]
                )
        );
    }

    /**
     * Obtain recipe preview data for recipe details modal.
     */
    public function getRecipePreview(int $recipeId, int $userId): JsonResponse
    {
        try {
            $user = User::findOrFail($userId);
            /**
             * @note eager loading calculations will query it much slower
             * @var \Illuminate\Database\Eloquent\Collection<int,Recipe> $recipe
             */
            $recipe = $user
                ->allRecipes()
                ->with(['ingestions', 'diets', 'seasons', 'tags.translations', 'steps'])
                ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
                ->select(
                    'recipes.*',
                    'user_recipe_calculated.ingestion_id AS calc_ingestion_id',
                    'user_recipe_calculated.recipe_data AS calc_recipe_data',
                    'user_recipe_calculated.invalid AS calc_invalid',
                    'user_recipe_calculated.updated_at AS calc_updated_at'
                )
                ->where('user_recipe_calculated.user_id', $userId)
                ->where('user_recipe.recipe_id', $recipeId)
                ->get();

            if ($recipe->isEmpty()) {
                throw new ModelNotFoundException();
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
        $ingestions            = Ingestion::getAll();
        $calculations          = [];
        $calculatedIngredients = [];
        $recipe->each(
            function (Recipe $recipe) use (&$calculations, &$calculatedIngredients, $ingestions, $user) {
                try {
                    $ingestionKey                         = $ingestions->where('id', $recipe->calc_ingestion_id)->first()->key;
                    $calculations[$ingestionKey]          = json_decode((string)$recipe->calc_recipe_data, true, 512, JSON_THROW_ON_ERROR);
                    $calculatedIngredients[$ingestionKey] = Calculation::parseRecipeData($recipe, $user->lang);
                } catch (\JsonException) {
                    return;
                }
            }
        );

        $recipe    = $recipe->first();
        $editRoute = route('admin.model.edit', ['adminModel' => 'recipes', 'adminModelId' => $recipe->id]);
        $view      = view(
            'admin::client.partials.recipe_modal_preview',
            [
                'editRoute'             => $editRoute,
                'recipe'                => $recipe,
                'calculations'          => $calculations,
                'calculatedIngredients' => $calculatedIngredients,
                'steps'                 => $recipe->steps
            ]
        );

        return response()->json(['success' => true, 'title' => $view->fragment('title'), 'data' => $view->fragment('card')]);
    }
}

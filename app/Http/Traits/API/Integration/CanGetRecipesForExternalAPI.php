<?php

namespace App\Http\Traits\API\Integration;

use App\Http\Resources\AOK\RecipeResource;
use App\Models\Ingestion;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection as DatabaseCollection;
use Illuminate\Http\JsonResponse;
use Modules\Ingredient\Models\Ingredient;

trait CanGetRecipesForExternalAPI
{
    private array $mealTime    = [];
    private array $seasons     = [];
    private array $ingredients = [];

    /**
     * Make a query to DB and return a recipe database collection.
     *
     * @param Authenticatable $user
     * @return DatabaseCollection
     */
    private function getRecipesCollection(Authenticatable $user): DatabaseCollection
    {
        $daysNew = config('foodpunk.days_recipe_is_new');

        $recipes = $user
            ->allRecipes()
            ->with(['diets', 'price', 'complexity', 'steps'])
            ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
            ->leftJoin(
                'user_excluded_recipes',
                function ($join) {
                    $join
                        ->on('recipes.id', '=', 'user_excluded_recipes.recipe_id')
                        ->on('user_recipe_calculated.user_id', '=', 'user_excluded_recipes.user_id');
                }
            )
            ->select(
                'recipes.*',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.ingestion_id AS calc_ingestion_id',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_excluded_recipes.recipe_id AS excluded',
            )
            ->selectRaw(
                "(DATEDIFF(CURDATE(), user_recipe.created_at) <= $daysNew AND user_recipe_calculated.invalid = 0) as is_new"
            )
            ->where('user_recipe_calculated.user_id', $user->id)
            ->where('user_recipe_calculated.invalid', 0)
            ->where('user_recipe.visible', true);

        $recipes = $recipes
//            ->groupBy('recipes.id')
            // do not need because we have ->where('user_recipe_calculated.invalid', 0)
//            ->orderBy('user_recipe_calculated.invalid', 'ASC')
            ->orderByRaw("is_new DESC")
            ->orderBy('user_excluded_recipes.recipe_id', 'ASC');

        return $recipes->get();
    }

    private function getRecipesData(): JsonResponse
    {
        $user = auth()->user();

        $recipeCollection = $this->getRecipesCollection($user);

        if ($recipeCollection->isEmpty()) {
            return $this->sendError(trans('common.not_exists_available_recipe'));
        }

        $recipes = $this->getUserRecipes($recipeCollection);

        return $this->sendResponse(RecipeResource::collection($recipes), trans('common.success'));
    }

    /**
     * Return recipes for a user.
     */
    private function getUserRecipes(DatabaseCollection $recipeCollection): array
    {
        $recipes        = [];
        $ingredientsIds = [];
        foreach ($recipeCollection as $recipeItem) {
            if (isset($recipeItem->calc_recipe_data) && ($recipeData = json_decode(
                $recipeItem->calc_recipe_data
            )) && !empty($recipeData->ingredients)) {
                foreach ($recipeData->ingredients as $ingredient) {
                    $ingredientsIds[] = $ingredient->id;
                }
            }
        }

        $this->setRecipeProperties($ingredientsIds);

        foreach ($recipeCollection as $recipeItem) {
            if (isset($recipeItem->calc_recipe_data) && ($recipeData = json_decode(
                $recipeItem->calc_recipe_data
            )) && !empty($recipeData->ingredients)) {
                if (empty($recipes[$recipeItem->id])) {
                    $recipes[$recipeItem->id] = [
                        'id'           => $recipeItem->id,
                        'title'        => $recipeItem->title,
                        'image'        => asset($recipeItem->image->url('original')),
                        'complexity'   => $recipeItem->complexity,
                        'price'        => $recipeItem->price,
                        'cooking_time' => $recipeItem->cooking_time,
                        'unit_of_time' => $recipeItem->unit_of_time,
                        'diets'        => $recipeItem->diets,
                        'steps'        => $recipeItem->steps()->get(),
                        'seasons'      => $this->getRecipeSeasons($recipeData->ingredients),
                        'meal_time'    => [
                            $recipeItem->calc_ingestion_id => [
                                'title'       => $this->getRecipeMealTime($recipeItem->calc_ingestion_id, 'title'),
                                'key'         => $this->getRecipeMealTime($recipeItem->calc_ingestion_id, 'key'),
                                'ingredients' => $this->getRecipeIngredients($recipeData->ingredients),
                                'nutrition'   => [
                                    'KH'   => $recipeData->calculated_KH,
                                    'EW'   => $recipeData->calculated_EW,
                                    'F'    => $recipeData->calculated_F,
                                    'KCal' => $recipeData->calculated_KCal
                                ]
                            ]
                        ]
                    ];
                } else {
                    $recipes[$recipeItem->id]['meal_time'][$recipeItem->calc_ingestion_id] = [
                        'title'       => $this->getRecipeMealTime($recipeItem->calc_ingestion_id, 'title'),
                        'key'         => $this->getRecipeMealTime($recipeItem->calc_ingestion_id, 'key'),
                        'ingredients' => $this->getRecipeIngredients($recipeData->ingredients),
                        'nutrition'   => [
                            'KH'   => $recipeData->calculated_KH,
                            'EW'   => $recipeData->calculated_EW,
                            'F'    => $recipeData->calculated_F,
                            'KCal' => $recipeData->calculated_KCal
                        ]
                    ];
                }
            }
        }

        return $recipes;
    }

    /**
     * Add name and unit properties for ingredient list.
     */
    private function getRecipeIngredients(array $recipeIngredients): array
    {
        foreach ($recipeIngredients as $ingredient) {
            if (!empty($this->ingredients[$ingredient->id])) {
                $ingredient->name = $this->ingredients[$ingredient->id]['name'];
                $ingredient->unit = $this->ingredients[$ingredient->id]['unit'];
            }
        }

        return $recipeIngredients;
    }

    /**
     * Return a meal time property.
     */
    private function getRecipeMealTime(int $id, string $property): string
    {
        return !empty($this->mealTime[$property][$id]) ? $this->mealTime[$property][$id] : $id;
    }

    /**
     * Return a list of seasons for an ingredient.
     */
    private function getIngredientSeasons(DatabaseCollection $recipeIngredients): array
    {
        $seasonIngredients = [];
        foreach ($recipeIngredients as $ingredient) {
            foreach ($ingredient->seasons as $season) {
                $seasonIngredients[$ingredient->id][] = $season->name;
            }
        }

        return $seasonIngredients;
    }

    /**
     * Return a list of seasons for a recipe.
     */
    private function getRecipeSeasons(array $ingredientCollection): array
    {
        $recipeSeasons = [];
        foreach ($ingredientCollection as $ingredient) {
            if (!empty($this->seasons[$ingredient->id])) {
                $recipeSeasons = array_merge($recipeSeasons, $this->seasons[$ingredient->id]);
            }
        }

        return array_unique($recipeSeasons);
    }

    /**
     * Set ingredients, seasons, meal times
     */
    private function setRecipeProperties(array $ingredientsIds): void
    {
        $ingredientCollection = Ingredient::with('seasons')->ofIds($ingredientsIds)->get();
        $this->seasons        = $this->getIngredientSeasons($ingredientCollection);
        $this->mealTime       = $this->getMealTimes();

        $this->ingredients = [];
        foreach ($ingredientCollection as $ingredient) {
            $this->ingredients [$ingredient->id] = [
                'name' => $ingredient->name,
                'unit' => $ingredient->unit->short_name
            ];
        }
    }

    /**
     * Return a list of meal times.
     */
    private function getMealTimes(): array
    {
        $mealTime            = [];
        $ingestionCollection = Ingestion::all();
        foreach ($ingestionCollection as $ingestion) {
            $mealTime['title'][] = $ingestion->title;
            $mealTime['key'][]   = $ingestion->key;
        }

        return $mealTime;
    }
}

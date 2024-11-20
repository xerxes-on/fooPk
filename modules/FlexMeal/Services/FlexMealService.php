<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Services;

use App\Exceptions\{NoData, PublicException};
use App\Models\{Ingestion, User};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Collection as EloquentCollection, ModelNotFoundException};
use Illuminate\Http\{UploadedFile};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\{Collection, Facades\DB};
use Modules\FlexMeal\Http\Requests\API\FlexMealUpdateRequest;
use Modules\FlexMeal\Http\Requests\UpdateFlexmealRequest;
use Modules\FlexMeal\Http\Resources\FlexMealResource;
use Modules\FlexMeal\Models\Flexmeal;
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\FlexMeal\Services\Calculations\FlexMealCalculator;

/**
 * Flexmeal service.
 *
 * @package App\Services
 */
final class FlexMealService
{
    /**
     * Create a flexmeal.
     */
    public function processStore(array $flexmealData, array $ingredients): int
    {
        $lastId = FlexmealLists::create($flexmealData)->id;

        foreach ($ingredients as $ingredient) {
            Flexmeal::create(
                [
                    'list_id'       => $lastId,
                    'amount'        => $ingredient['amount'],
                    'ingredient_id' => $ingredient['ingredient_id']
                ]
            );
        }

        return $lastId;
    }

    /**
     * Delete a flexmeal.
     *
     * @throws NoData
     * @throws PublicException
     */
    public function processDelete(User $user, int $flexmealID): void
    {
        $flexmeal = $user->flexmealLists()
            ->where('id', $flexmealID)
            ->first();

        if (is_null($flexmeal)) {
            throw new NoData(trans('common.list_not_found'));
        }

        $meals = $user->meals()
            ->where('flexmeal_id', $flexmeal->id)
//					  ->where('challenge_id', $user->subscription?->id)
            ->orderBy('ingestion_id')
            ->get();

        // collect recipe IDS that already excluded, so they will be ignored from available recipes
        $excludedRecipesIds = $user->excludedRecipes()->pluck('recipe_id')->toArray();

        try {
            DB::transaction(
                static function () use ($meals, $excludedRecipesIds) {
                    foreach ($meals as $meal) {
                        $meal->replaceWithRandom($excludedRecipesIds);
                    }
                },
                3
            );
        } catch (PublicException $e) {
            throw $e;
        } catch (\Throwable $e) {
            logError($e);
            throw new PublicException(trans('api.exclude_meal_public_error'));
        }

        $flexmeal->delete();
    }

    /**
     * Retrieve FlexMeal list with used ingredients
     */
    public function getFlexMealWithIngredientsMap(
        User $user,
        bool $ingredientsAsCollection = false
    ): Collection|EloquentCollection {
        $mealList        = $user->flexmealLists;
        $ingredientsUsed = Flexmeal::whereIn('list_id', $mealList->pluck('id'))->with('ingredient')->get();

        if ($ingredientsAsCollection) {
            $ingredientsUsed = FlexMealResource::collection($ingredientsUsed);
        }

        return $mealList->map(
            function ($value) use ($ingredientsUsed) {
                $values = collect();

                foreach ($ingredientsUsed as $item) {
                    if ($item->list_id === $value->id) {
                        $values->push($item);
                    }
                }

                $value->used_ingredients = $values;
                return $value;
            }
        );
    }

    /**
     * Retrieve FlexMeal list grouped by ingestions with used and calculated ingredients.
     */
    public function getFlexMealByIngestionWithIngredientsMap(User $user, string|array $mealtime): LengthAwarePaginator
    {
        $mealList = $user->flexmealLists();

        if (is_string($mealtime)) {
            $mealList = $mealList->whereMealtime($mealtime);
        } elseif (is_array($mealtime)) {
            $mealList = $mealList
                ->whereIn('mealtime', $mealtime)
                ->orderByRaw("FIELD(mealtime, ?, ?)", $mealtime);
        }

        $mealList = $mealList->paginate(20);

        $usedMealListsIds = $mealList->getCollection()->pluck('id');
        $ingredientsUsed  = Flexmeal::whereIn('list_id', $usedMealListsIds)->with('ingredient')->get();

        $mealList->getCollection()->transform(
            function (FlexmealLists $flexMealItem) use ($ingredientsUsed) {
                $values = collect();

                foreach ($ingredientsUsed as $item) {
                    if ($item->list_id === $flexMealItem->id) {
                        $item->ingredient->calories = round(
                            $item->ingredient->fats * 9 + $item->ingredient->carbohydrates * 4 + $item->ingredient->proteins * 4,
                            2
                        );

                        $values->push($item);
                    }
                }

                $flexMealItem->used_ingredients     = $values;
                $flexMealItem->calculated_nutrients = (new FlexMealCalculator())($values);
                return $flexMealItem;
            }
        );

        return $mealList;
    }

    /**
     * Handle update of FlexMeal list over API.
     *
     * @throws ModelNotFoundException|PublicException
     */
    public function processUpdateOverAPI(FlexMealUpdateRequest $request, int $list_id): void
    {
        // We need to replace all meals where this flexmeal has been used only in specific conditions
        if (!$request->regenerateMealPlan) {
            $list = $this->handleUpdate($request, $list_id);

            $image = $request->file('new_image');
            if (!is_null($image)) {
                $list->image = $image;
            }

            $list->update();
            return;
        }

        $meals = $request->user
            ->meals()
            ->setEagerLoads([])
            ->where('flexmeal_id', $list_id)
            ->orderBy('ingestion_id')
            ->get();

        // collect recipe IDS that already excluded, so they will be ignored from available recipes
        $excludedRecipesIds = $request->user->excludedRecipes()->pluck('recipe_id')->toArray();
        try {
            DB::transaction(
                function () use ($request, $meals, $excludedRecipesIds, $list_id) {
                    // First its required to replace all meals where this flexmeal has been used
                    foreach ($meals as $meal) {
                        $meal->replaceWithRandom($excludedRecipesIds);
                    }

                    // Second, update the data itself
                    $list = $this->handleUpdate($request, $list_id);

                    $image = $request->file('new_image');
                    if (!is_null($image)) {
                        $list->image = $image;
                    }

                    $list->update();
                },
                config('database.transaction_attempts')
            );
        } catch (\Throwable $e) {
            logError($e);
            throw new PublicException(trans('common.unexpected_error'));
        }
    }

    /**
     * Handle update of FlexMeal list over WEB.
     *
     * @throws ModelNotFoundException|PublicException
     */
    public function processUpdateOverWeb(UpdateFlexmealRequest $request): void
    {
        // We need to replace all meals where this flexmeal has been used only in specific conditions
        if (!$request->regenerateMealPlan) {
            $list = $this->handleUpdate($request, (int)$request->id);

            if (empty($request->old_image)) {
                $list->image = STAPLER_NULL;
            } elseif ($request->hasFile('image')) {
                $list->image = $request->file('image');
            }

            $list->update();
            return;
        }

        $meals = $request->user
            ->meals()
            ->setEagerLoads([])
            ->where('flexmeal_id', $request->id)
            ->orderBy('ingestion_id')
            ->get();

        // collect recipe IDS that already excluded, so they will be ignored from available recipes
        $excludedRecipesIds = $request->user->excludedRecipes()->pluck('recipe_id')->toArray();
        try {
            DB::transaction(
                function () use ($request, $meals, $excludedRecipesIds) {
                    // First its required to replace all meals where this flexmeal has been used
                    foreach ($meals as $meal) {
                        $meal->replaceWithRandom($excludedRecipesIds);
                    }

                    // Second, update the data itself
                    $list = $this->handleUpdate($request, $request->id);

                    if (empty($request->old_image)) {
                        $list->image = STAPLER_NULL;
                    } elseif ($request->hasFile('image')) {
                        $list->image = $request->file('image');
                    }

                    $list->update();
                },
                config('database.transaction_attempts')
            );
        } catch (\Throwable $e) {
            logError($e);
            throw new PublicException(trans('common.unexpected_error'));
        }
    }

    /**
     * Handle update of FlexMeal list.
     *
     * @throws ModelNotFoundException
     */
    private function handleUpdate(FlexMealUpdateRequest|UpdateFlexmealRequest $request, int $flexMealListID): FlexmealLists
    {
        $list                  = $request->listModel;
        $uniqueIngredientsList = $this->getUniqueListOfIngredients($request->ingredients, $flexMealListID);
        // Remove old ingredients and insert new ones
        Flexmeal::whereIn('list_id', [$flexMealListID])->delete();
        Flexmeal::insert($uniqueIngredientsList);

        $list->mealtime = $request->get('meal', $list->mealtime);
        $list->name     = $request->get('flexmeal', $list->name);
        $list->notes    = $request->get('notes', $list->notes);

        return $list;
    }

    /**
     * Process update of FlexMealList image.
     *
     * @throws ModelNotFoundException
     */
    public function processImageUpdate(UploadedFile $image, int $list_id): void
    {
        $list        = FlexmealLists::findOrFail($list_id);
        $list->image = $image;
        $list->update();
    }

    /**
     * Generate array of unique ingredients.
     */
    private function getUniqueListOfIngredients(array $ingredients, int $list_id): array
    {
        $return = [];

        foreach ($ingredients as $item) {
            $return[(int)$item['ingredient_id']] = [
                'list_id'       => $list_id,
                'amount'        => $item['amount'],
                'ingredient_id' => (int)$item['ingredient_id'],
            ];
        }
        return array_values($return);
    }

    /**
     * Replace recipe of a meal with a flexmeal recipe.
     *
     * @throws PublicException
     */
    public function replaceWithFlexMeal(
        User          $user,
        Ingestion     $ingestion,
        Carbon        $date,
        FlexmealLists $flexmeal,
    ): void {
        $meal = $user->meals()
            ->where('ingestion_id', $ingestion->id)
            ->whereDate('meal_date', $date)
            ->first();

        if (is_null($meal)) {
            $day = $date->format('Y-m-d');
            throw new PublicException("There's no $ingestion->title on $day.");
        }

        // meal update
        $meal->recipe_id        = null;
        $meal->custom_recipe_id = null;
        $meal->flexmeal_id      = $flexmeal->id;
        $meal->save();
    }
}

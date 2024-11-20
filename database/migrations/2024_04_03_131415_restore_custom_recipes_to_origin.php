<?php

declare(strict_types=1);

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Exceptions\PublicException;
use App\Helpers\Calculation;
use App\Models\CustomRecipe;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function down(): void
    {

    }


    private function restoreRecipe($customRecipe)
    {

        $shoppingListService = app(\Modules\ShoppingList\Services\ShoppingListRecipesService::class);
        $recipeService       = app(\App\Services\RecipeService::class);

        $user = User::find($customRecipe->user_id);
        try {
            $meal             = $user->meals()->where('custom_recipe_id', $customRecipe->id)->firstOrFail();
            $originalRecipeId = $meal->original_recipe_id ?? CustomRecipe::findOrFail($meal->custom_recipe_id)->recipe_id;


            $message               = '';
            $type                  = null;
            $existedInShoppingList = false;
            $mealtimeIntValue      = MealtimeEnum::tryFromValue($meal->meal_time)->value;
            // Attempt to delete old recipe from shopping list
            try {
                $shoppingListService->deleteRecipe(
                    $user,
                    $meal->custom_recipe_id,
                    RecipeTypeEnum::CUSTOM->value,
                    $meal->meal_date,
                    $mealtimeIntValue
                );
                $existedInShoppingList = true;
            } catch (ModelNotFoundException) {
                // Recipe is not in shopping list, nothing to delete
                $message .= trans('common.purchase_list.messages.errors.not_found_while_deleting');
                $type = 'info';
            } catch (PublicException $e) {
                $message .= $e->getMessage();
                $type = 'error';
            } catch (\InvalidArgumentException $e) {
                logError($e);
            }

            // Restore custom to original recipe
            $recipeService->restore($meal, $originalRecipeId);


            if ($existedInShoppingList) {
                try {
                    $shoppingListService->addRecipe(
                        $user,
                        $originalRecipeId,
                        RecipeTypeEnum::ORIGINAL->value,
                        $meal->meal_date,
                        $mealtimeIntValue
                    );
                } catch (PublicException|\InvalidArgumentException $e) {
                    $message .= $e->getMessage();
                    $type = 'error';
                }
            }

            dump('Restored,' . $customRecipe->id);
        } catch (ModelNotFoundException) {

        }


        // Attempt to add new recipe to shopping list

    }

    /**
     * Reverse the migrations.
     */
    public function up(): void
    {
        $customRecipes = CustomRecipe::where('created_at', '>', '2024-04-02 11:00:00')->where('created_at', '<', '2024-04-03 00:00:00')->where('updated_at', '<', '2024-04-03 00:00:00')->whereNotNull('recipe_id')->get();

        $customRecipesInRiskZone = [];
        foreach ($customRecipes as $customRecipe) {
            // strange behaviour....
            //            $customRecipeIngredientsAmount = $customRecipe->ingredients()->count();
            $originalRecipe      = Recipe::find($customRecipe->recipe_id);
            $fixedIngredients    = $originalRecipe->ingredients()->count();
            $variableIngredients = $originalRecipe->variableIngredients()->count();

            $customRecipeCalculationData = DB::table('user_recipe_calculated')
                ->select('recipe_data as calc_recipe_data')
                ->where('user_id', $customRecipe->user_id)
                ->where('custom_recipe_id', $customRecipe->id)
                ->first();

            if (!is_null($customRecipeCalculationData)) {
                $calculatedIngredients = Calculation::parseRecipeData($customRecipeCalculationData);

                $customRecipeIngredientsAmount = count($calculatedIngredients);

                if ($customRecipeIngredientsAmount != ($fixedIngredients + $variableIngredients) || $customRecipeIngredientsAmount < 3) {
                    dump('------------', $customRecipe->recipe_id, $customRecipeIngredientsAmount, $customRecipe->id, $fixedIngredients, $variableIngredients);
                    //                    dump('------------', $customRecipe->recipe_id,  $customRecipe->id, $fixedIngredients, $variableIngredients);
                    $this->restoreRecipe($customRecipe);
                    $customRecipe->delete();
                    $customRecipesInRiskZone[$customRecipe->id] = $customRecipe->id;
                }
            }


        }

        $keys = array_keys($customRecipesInRiskZone);
        sort($keys);
        dump('deleted', $keys);
    }
};

<?php

namespace App\Admin\Http\Controllers\Recipe;

use App\Admin\Services\JobCheckService;
use App\Events\AdminActionsTaken;
use App\Exceptions\NoData;
use App\Helpers\Calculation;
use App\Http\Controllers\Controller;
use App\Jobs\RecalculateForAllUsers;
use App\Jobs\RecalculateRecipes;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserRecipeCalculatedPreliminary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Ingredient\Models\Ingredient;

/**
 * Controller for various recipe calculations
 *
 * @package App\Admin\Http\Controllers\Recipe
 */
class RecipeCalculationsAdminController extends Controller
{
    /**
     * Calculate variable ingredients matrix
     * TODO: provide form request
     */
    public function calculateVariableIngredients(Request $request): mixed
    {
        $common_id = [];
        if (isset($request->ingredients['common'])) {
            foreach ($request->ingredients['common'] as $ingredient) {
                $common_id[] = $ingredient['id'];
            }
        }
        $variable_id = $request->ingredients['variable'] ?? [];

        $recipe = [
            'ingradients'          => Ingredient::whereIn('id', $common_id)->get()->toArray(),
            'ingradients_variable' => Ingredient::whereIn('id', $variable_id)->get()->toArray(),
        ];

        // calculation method Calculation::calculateRecipe require unit_data for every ingredient
        foreach ($recipe['ingradients'] as $key => $ingradient) {
            $recipe['ingradients'][$key]['unit_data'] = $recipe['ingradients'][$key]['unit'];
        }
        foreach ($recipe['ingradients_variable'] as $key => $ingradient) {
            $recipe['ingradients_variable'][$key]['unit_data'] = $recipe['ingradients_variable'][$key]['unit'];
        }

        //getting amount of static ingredients for recipe
        if (!empty($request->ingredients['common'])) {
            foreach ($request->ingredients['common'] as $front_information) {
                $ingredientArrayKey = array_search(
                    $front_information['id'],
                    array_column($recipe['ingradients'], 'id')
                );
                if ($ingredientArrayKey !== false) {
                    $recipe['ingradients'][$ingredientArrayKey]['amount'] = (int)$front_information['amount'];
                }
            }
        }
        return Calculation::calculateRecipe($recipe, (int)$request->kcal, (int)$request->kh);
    }

    /**
     * Get recipe diets
     */
    public function calculateRecipeDiets(Request $request): array
    {
        return Recipe::calculateRecipeDiets($request->ingredients_id, true);
    }

    /**
     * Recalculate recipe to user.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function recalculate2user(Request $request): JsonResponse
    {
        $user   = User::findOrFail((int)$request->get('userId'));
        $result = [
            'success' => false,
            'message' => trans('common.fill_formular_message')
        ];

        # check formular answer
        if ($user->isQuestionnaireExist() && !empty($user->dietdata) && $user->questionnaireApproved === true) {
            # get recipe Ids
            $recipeIds = $user->allRecipes()->orderBy('recipes.id')->pluck('recipes.id')->toArray();
            RecalculateRecipes::dispatch($user, $recipeIds)
                ->onQueue('high')
                ->delay(now()->addSeconds(5));

            # user recipe Calculated Preliminary nilled
            if ($user->preliminaryCalc()->count() > 0) {
                UserRecipeCalculatedPreliminary::whereUserId($user->id)->update(['valid' => null, 'counted' => 0]);
            }

            $result = [
                'success' => true,
                'message' => 'Recalculation running in the background!'
            ];
        }

        AdminActionsTaken::dispatch();

        return response()->json($result);
    }

    /**
     * Recalculation for all users by recipe
     */
    public function recalculateForAllUsers(Request $request, JobCheckService $service): JsonResponse
    {
        $recipeId = (int)$request->get('recipeId');
        $userIds  = DB::table('user_recipe')
            ->where('recipe_id', $recipeId)
            ->pluck('user_id')
            ->toArray();
        $result = [
            'success' => false,
            'message' => 'Recalculation is in progress, please wait!'
        ];

        if ($service->checkJobRun(RecalculateForAllUsers::class, 'recipeId', $recipeId) === false) {
            RecalculateForAllUsers::dispatch($userIds, $recipeId)
                ->onQueue('high')
                ->delay(now()->addSeconds(5));

            $result = [
                'success' => true,
                'message' => 'Recalculation running in the background!'
            ];
        }

        AdminActionsTaken::dispatch();

        return response()->json($result);
    }

    /**
     * Generate report on user calculations jobs
     */
    public function checkJobStatus(Request $request, int $userId, JobCheckService $service): JsonResponse
    {
        try {
            $availableJobs = $service->getUserRecalculationJobs($userId);
            $response      = '<ul class="list-unstyled m-0">';
            foreach ($availableJobs as $job) {
                $response .= sprintf('<li>%s%s</li>', $job['status'], $job['time']);
            }
            $response .= '</ul>';

            return response()->json(['success' => true, 'status' => 'warning', 'message' => $response]);
        } catch (NoData $e) {
            return response()->json(['success' => true, 'status' => 'success', 'message' => '<p class="m-0">' . $e->getMessage() . '</p>']);
        }
    }
}

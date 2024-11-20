<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers\Recipe;

use AdminSection;
use App\Admin\Http\Requests\Client\ClientAddRandomRecipeRequest;
use App\Admin\Http\Requests\Recipe\SearchRecipeRequest;
use App\Admin\Http\Requests\RecipeFormRequest;
use App\Enums\Admin\Permission\PermissionEnum;
use App\Events\{AdminActionsTaken};
use App\Helpers\Calculation;
use App\Http\Controllers\Controller;
use App\Jobs\ReplaceDraftRecipeInMealPlanJob;
use App\Models\{Recipe, RecipeTag, Seasons, User};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth};
use Illuminate\View\View;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;

/**
 * Controller for recipes.
 *
 * @package App\Http\Controllers\Admin
 */
final class RecipeAdminController extends Controller
{
    /**
     * Store a recipe.
     *
     * TODO: fix -> Implicit conversion from float 67.63999999999999 to int loses precision in /vendor/imagine/imagine/src/Gd/Image.php on line 148
     */
    public function store(RecipeFormRequest $request): RedirectResponse
    {
        $model = Recipe::updateOrCreate(['id' => $request->id], $request->validated());

        $model->saveRelatedRecipes($request->related)
            ->saveRecipeInventory($request->inventory)
            ->saveRecipeIngestions($request->ingestions)
            ->saveRecipeDietCategory(Recipe::calculateRecipeDiets($request->ingredientIds))
            ->saveRecipeIngredients($request->ingredients)
            ->saveRecipeVariableIngredients($request->variable_ingredients)
            ->syncRecipeSteps($request->steps)
            ->saveRecipeSeasons();

        // TODO: @Nick, need a job for outdated recipes
        if (!is_null($request->id) && $model->status->isDraft()) {
            ReplaceDraftRecipeInMealPlanJob::dispatch($model->id);
        }

        $message = is_null($request->id) ? 'record_created_successfully' : 'record_updated_successfully';

        AdminActionsTaken::dispatch();

        return redirect()
            ->route('admin.model.edit', ['adminModel' => 'recipes', 'adminModelId' => $model->id])
            ->with('success_message', trans('common.' . $message));
    }

    /**
     * Search for a recipe.
     * @throws \Throwable
     */
    public function searchRecipe(SearchRecipeRequest $request): string
    {
        return view('admin::recipe.searchResult', [
            'recipes' => Recipe::searchBy($request->filters)
                ->with(
                    [
                        'ingestions' => static fn(BelongsToMany $relation) => $relation->withOnly('translations'),
                        'diets',
                        'complexity',
                        'tags.translations'
                    ]
                )
                ->paginate(20),
        ])->render();
    }

    /**
     * Copy recipe by id.
     */
    public function copyRecipe(mixed $id): RedirectResponse
    {
        $original = Recipe::with('ingredients', 'variableIngredients', 'steps', 'diets', 'inventories', 'ingestions')
            ->findOrFail($id);
        $clone = $original->replicate();
        /**
         * TODO: modify correctly later
         * somehow this method is doesn't work for replication,
         * example was found  vendor/astrotomic/laravel-translatable/tests/TranslatableTest.php::replicate_entity()
         * $clone->replicateWithTranslations();
         * trick for copy translations
         */
        $translationsArray = $clone->getTranslationsArray();
        unset($clone->translations);
        $preparedTranslations = [];
        foreach ($translationsArray as $lang => $values) {
            foreach ($values as $key => $value) {
                $preparedTranslations[$key . ':' . $lang] = $value;
            }
        }
        $clone->fill($preparedTranslations);
        // end of trick for copy translations

        $clone->title = '*COPY* ' . $original->title;
        $clone->push();

        $path = 'uploads/recipe/';
        $this->copyImages($path . $original->id, $path . $clone->id, false);

        $steps = [];
        foreach ($original->steps()->get() as $step) {
            $steps[]['description'] = $step->description;
        }

        $ingredients = [];
        foreach ($original->ingredients as $ingredient) {
            $ingredients[] = [
                'ingredient_id' => $ingredient->id,
                'amount'        => $ingredient->pivot->amount
            ];
        }

        $variable_ingredients = [];
        foreach ($original->variableIngredients as $ingredient) {
            $variable_ingredients[] = [
                'ingredient_id' => $ingredient->id,
                'category_id'   => $ingredient->pivot->ingredient_category_id
            ];
        }

        $ingestions = [];
        foreach ($original->ingestions as $ingestion) {
            $ingestions[] = $ingestion->id;
        }

        $inventories = [];
        foreach ($original->inventories as $inventory) {
            $inventories[] = $inventory->id;
        }

        $diets = [];
        foreach ($original->diets as $diet) {
            $diets[] = $diet->id;
        }

        $clone->saveRecipeSteps($steps)
            ->saveRecipeIngredients($ingredients)
            ->saveRecipeVariableIngredients($variable_ingredients)
            ->saveRecipeIngestions($ingestions)
            ->saveRecipeInventory($inventories)
            ->saveRecipeDietCategory($diets)
            ->saveRecipeSeasons();

        AdminActionsTaken::dispatch();

        return redirect()->route('admin.model.edit', ['adminModel' => 'recipes', 'adminModelId' => $clone->id]);
    }

    /**
     * Copy folder with files into new folder.
     */
    private function copyImages(string $from, string $to, bool $rewrite = true): void
    {
        if (file_exists($from)) {
            if (is_dir($from)) {
                @mkdir($to);
                $d = dir($from);
                while (false !== ($entry = $d->read())) {
                    if ($entry == "." || $entry == "..") {
                        continue;
                    }
                    $this->copyImages("$from/$entry", "$to/$entry", $rewrite);
                }
                $d->close();
            } else {
                if (!file_exists($to) || $rewrite) {
                    copy($from, $to);
                }
            }
        }
    }

    /**
     * Adding recipe to user.
     */
    public function addRecipe2user(Request $request): JsonResponse
    {
        # get all user Ids
        $result = [
            'success' => true,
            'message' => null
        ];

        $users = User::whereIn('id', $request->userIds)->get();
        foreach ($users as $user) {
            /** @var User $user */
            # check formular answer
            if ($user->isQuestionnaireExist()) {
                # calc recipe to user processed
                $calcResult = Calculation::_calcRecipe2user($user, $request->recipeIds, true, ['skip_related_recipes' => true]);

                $result['success'] = $calcResult['success'];
                $result['message'] .= '<br><b>User #' . $user->id . '</b><br>Requested recipes ID: ' . implode(
                    ', ',
                    $request->recipeIds
                ) . '; ' . $calcResult['message'];
                continue;
            }

            $result['success'] = false;
            $result['message'] .= '<br>User #' . $user->id . ' => ' . trans('common.fill_formular_message');
        }

        return response()->json($result);
    }

    /**
     * Add random recipe to user.
     */
    public function addRandomRecipe2user(ClientAddRandomRecipeRequest $request): JsonResponse
    {
        $users = User::whereIn('id', $request->userIds)->get();


        # get allowed seasons
        $seasons = false;
        if (!empty($request->seasons)) {
            $seasons = Seasons::whereIn('id', $request->seasons)->orderBy(
                'key',
                'ASC'
            )->get()->pluck('name')->toArray();
        }

        # result array
        $_result = [
            'success' => true,
            'message' => null,
            'console' => ''
        ];
        $options = [
            'amountRecipes'    => $request->amount,
            'distributionType' => $request->distribution_type,
            'breakfastSnack'   => $request->breakfast_snack,
            'lunchDinner'      => $request->lunch_dinner,
        ];

        if (!empty($request->recipes_tag)) {
            $recipeTag = RecipeTag::find($request->recipes_tag);
            if ($recipeTag) {
                $options['recipes_tag']           = $recipeTag->getKey();
                $options['recipes_tag_selection'] = Calculation::RECIPE_DISTRIBUTION_FROM_TAG_TYPE_STRICT;
            }
        }

        if (!empty($request->distribution_mode)) {
            $options['distribution_mode'] = $request->distribution_mode;
        }

        foreach ($users as $user) {

            // WEB-747 issue
            // resync excluded ingredients
            SyncUserExcludedIngredientsJob::dispatchSync($user);

            /** @var User $user */
            # check formular answer
            if (!$user->isQuestionnaireExist()) {
                $_result['success'] = false;
                $_result['message'] .= '<br>User #' . $user->id . ' => ' . trans('common.fill_formular_message');
                continue;
            }

            $count = Calculation::_addRandomRecipe2user($user, $request->amount, $request->seasons, null, $options);

            if ($options['distributionType'] == 'general') {
                $_result['message'] .= '<br>Recipes have been added to User #' . $user->id;
                $_result['console'] .= 'Recipes have been added to User #' . $user->id;
                if (!empty($request->seasons)) {
                    $_result['message'] .= ' for seasons:' . custom_implode($seasons);
                    $_result['console'] .= ' for seasons:' . custom_implode($seasons);
                }

                $_result['message'] .= '| ' . $count . ' of ' . $request->amount . '<br/>';
                $_result['console'] .= '| ' . $count . ' of ' . $request->amount . "\n";
            } elseif ($options['distributionType'] == 'ingestions') {
                $_result['message'] .= '<br>Recipes have been added to User #' . $user->id;
                $_result['console'] .= "\n" . 'Recipes have been added to User #' . $user->id;
                if (!empty($seasonsRecipes)) {
                    $_result['message'] .= ' for seasons:' . custom_implode($seasons);
                    $_result['console'] .= ' for seasons:' . custom_implode($seasons);
                }

                if (!empty($options['ingestions_scope'])) {
                    $_result['message'] .= '<br/><br/>';
                    $_result['console'] .= "\n";
                    foreach ($options['ingestions_scope'] as $ingestionData) {
                        $textColor = 'black';
                        if ($ingestionData['count_distributed'] < $ingestionData['count_requested']) {
                            $textColor = 'red';
                        }

                        $_result['message'] .= ' ' . $ingestionData['ingestion_key'] . ': <span style="color:' . $textColor . '">' . $ingestionData['count_distributed'] . ' of ' . $ingestionData['count_requested'] . '</span><br/>';

                        $recipesIds = array_column($ingestionData['recipes'], 'recipe_id');
                        sort($recipesIds);

                        $_result['console'] .= $ingestionData['ingestion_key'] . ': ' . $ingestionData['count_distributed'] . ' of ' . $ingestionData['count_requested'];
                        if (!empty($recipesIds)) {
                            $_result['console'] .= ' Recipe ids: ' . custom_implode($recipesIds);
                        }

                        $_result['console'] .= (($textColor == 'red') ? '!!!' : '') . "\n";
                    }
                }
            }

            if ($count < $request->amount) {
                $_result['success'] = false;
            }

            $_result['message'] .= ' Distribution mode: <b>' . $request->distribution_mode . "</b><br/>";
            $_result['console'] .= ' Distribution mode: ' . $request->distribution_mode . "\n";

            if (isset($recipeTag)) {
                $_result['message'] .= ' Recipe tag: <b>' . $recipeTag->title . "</b><br/>";
                $_result['console'] .= ' Recipe tag:  ' . $recipeTag->title . "\n";
            }
        }

        AdminActionsTaken::dispatch();

        return response()->json($_result);
    }

    /**
     * Generate recipe to Subscribe
     */
    public function generate2subscription(Request $request): JsonResponse
    {
        # get user by id
        $user = User::findOrFail($request->get('userId'));

        # check formular answer
        if (!$user->isQuestionnaireExist()) {
            $result = [
                'success' => false,
                'message' => trans('common.fill_formular_message')
            ];
        } elseif (empty($user->subscription)) {
            // TODO: are we checking for active subscription or any or course?
            $result = [
                'success' => false,
                'message' => trans('course::common.no_active_course')
            ];
        } else {
            # ============================
            # generate recipe to Subscribe
            # ============================

            $result = Calculation::_generate2subscription($user);
        }

        return response()->json($result);
    }

    /**
     * Import recipes.
     * @note not working now...don't know when will
     */
    public function import(): RedirectResponse|View
    {
        if (!Auth::user()?->hasPermissionTo(PermissionEnum::IMPORT_RECIPE->value)) {
            return redirect()->back();
        }

        $content = 'Import recipes page (In development)';

        return AdminSection::view($content, 'Import recipes');
    }
}

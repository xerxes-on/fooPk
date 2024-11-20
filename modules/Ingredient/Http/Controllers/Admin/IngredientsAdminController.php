<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Controllers\Admin;

use AdminSection;
use App\Enums\Admin\Permission\PermissionEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Exceptions\PublicException;
use App\Http\Controllers\Controller;
use App\Jobs\RecalculateRecipeDiets;
use App\Models\{Recipe, Seasons, User};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\View\View;
use Modules\Ingredient\Http\Requests\Admin\IngredientFormRequest;
use Modules\Ingredient\Http\Requests\Admin\SearchIngredientRequest;
use Modules\Ingredient\Http\Resources\IngredientWithTagsSearchSelect2Resource;
use Modules\Ingredient\Jobs\RecalculateUsersForbiddenIngredientsJob;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;
use Modules\Ingredient\Models\IngredientHint;
use Modules\Ingredient\Services\IngredientSearchService;
use Modules\ShoppingList\Models\ShoppingListIngredient;

/**
 * Controller for ingredients.
 *
 * @package Modules\Ingredient\Http\Controllers\Admin
 */
final class IngredientsAdminController extends Controller
{
    public function store(IngredientFormRequest $request): RedirectResponse
    {
        // get all values
        $values = $request->validated();

        // if update ingredient -> check category changes
        if (!is_null($request->id)) {
            $this->updateIngredientCategory((int)$request->id, (int)$request->category_id);
        }

        // Save ingredient
        $ingredient = Ingredient::updateOrCreate(['id' => $request->id], $values);

        // Save ingredient vitamins
        $vitaminsToSave = [];
        foreach ($request->get('vitamins', []) as $vitaminId => $value) {
            $vitaminsToSave[$vitaminId] = [
                'value' => $value ?? 0,
            ];
        }
        $ingredient->vitamins()->sync($vitaminsToSave);

        $message     = is_null($request->id) ? trans('common.record_created_successfully') : trans('common.record_updated_successfully');
        $messageType = 'success_message';
        // Process and store seasons
        try {
            $this->storeIngredientSeasons($ingredient, $request->seasons);
        } catch (PublicException $e) {
            $message .= ' But ' . $e->getMessage();
            $messageType = 'warning_message';
        }

        // Save tags
        $ingredient->tags()->sync($request->tags);

        $this->storeIngredientHint($ingredient, $request->hint);

        return redirect()->back()->with($messageType, $message);
    }

    /**
     * Handle ingredient seasons store.
     *
     * @throws PublicException
     */
    private function storeIngredientSeasons(Ingredient $ingredient, ?array $seasons = null): void
    {
        $seasons = is_array($seasons) ?
            array_filter(
                array_map('intval', $seasons),
                static function ($item) {
                    if ((int)$item !== Seasons::ANY_SEASON_ID) {
                        return (int)$item;
                    }
                }
            ) :
            [Seasons::ANY_SEASON_ID];

        if ([] === $seasons) {
            $seasons = [Seasons::ANY_SEASON_ID];
        }

        $ingredient->seasons()->sync($seasons);
        $desiredID = $ingredient->id;

        // Now we need to recalculate all recipes seasons
        try {
            DB::transaction(static function () use ($desiredID) {
                $recipes = Recipe::whereHas('ingredients', static function ($q) use ($desiredID) {
                    $q->where('ingredient_id', $desiredID);
                })->orWhereHas('variableIngredients', function ($q) use ($desiredID) {
                    $q->where('ingredient_id', $desiredID);
                })->distinct()->get(['id']);

                if ($recipes->isEmpty()) {
                    return;
                }

                $recipes->each(static function (Recipe $recipe) {
                    $recipe->saveRecipeSeasons();
                });
            }, (int)config('database.transaction_attempts'));
        } catch (\Throwable $e) {
            logError($e);
            throw new PublicException('Unable to recalculate recipes seasons');
            // TODO: maybe make a job to run in background or smth
        }
    }

    private function storeIngredientHint(Ingredient $ingredient, array $data): void
    {
        $hint = $ingredient->hint()->first(['id']);
        if (is_null($hint)) {
            $hint = new IngredientHint();
        }
        $hint->content   = $data['content'] ?? '';
        $hint->link_text = $data['link_text'] ?? '';
        $hint->link_url  = $data['link_url'] ?? '';

        $hint->ingredient()->associate($ingredient)->save();
    }

    /**
     * Update ingredient category
     */
    private function updateIngredientCategory(int $ingredientId, int $categoryId): void
    {
        // get ingredient
        $ingredientInTable = Ingredient::findOrFail($ingredientId);

        // if category changes -> update ingredient information
        if ($ingredientInTable->category_id === $categoryId) {
            return;
        }
        // get category
        $category = IngredientCategory::find($categoryId);

        // recalculate all recipes diets
        RecalculateRecipeDiets::dispatch($ingredientId);
        RecalculateUsersForbiddenIngredientsJob::dispatch();

        // update purchase list ingredients categories
        ShoppingListIngredient::whereIngredientId($ingredientId)
            ->update(
                [
                    'category_id' => !is_null($category?->tree_information['mid_category']) ?
                        $category->tree_information['mid_category'] :
                        $categoryId
                ]
            );
    }

    /**
     * Search for an ingredient.
     * @throws \Throwable
     */
    public function searchIngredient(SearchIngredientRequest $request): string
    {
        return view('admin::ingredient.searchResult', [
            'ingredients' => Ingredient::with(['seasons', 'tags'])->searchBy($request->filters)->paginate(20)
        ])->render();
    }

    /**
     * Import Ingredients.
     */
    public function import(): RedirectResponse|View
    {
        if (!\Auth::user()?->hasPermissionTo(PermissionEnum::IMPORT_INGREDIENT->value)) {
            return redirect()->back();
        }

        $content = 'Import ingredients page (In development)';
        return AdminSection::view($content, 'Import ingredients');
    }

    /**
     * Perform search ingredients by ajax from Select2.
     */
    public function searchIngredientsAjax(Request $request): JsonResponse
    {
        $searchVal = $request->q;

        if (is_null($searchVal)) {
            return response()->json();
        }

        $getAll = is_null(RequestFacade::route('all'));
        // If condition is true we suppose that user tries to find ingredients by title
        $query = $getAll ? Ingredient::withOnly(['translations', 'unit', 'diets']) : Ingredient::withOnly(['translations']);
        $query = (int)$searchVal === 0 ?
            $query->whereTranslationLike('name', "%$searchVal%") :
            $query->where('id', 'like', "%$searchVal%");

        // Format response for select2 format
        $data = $getAll ? $query->get() : $query->get(['id']);

        return response()->json(
            $getAll ?
                [
                    'results' => $data->map(
                        fn(Ingredient $item) => [
                            'id'   => $item->id,
                            'text' => $item->name,
                            'data' => [
                                'proteins'          => $item->proteins,
                                'fats'              => $item->fats,
                                'carbohydrates'     => $item->carbohydrates,
                                'calories'          => $item->calories,
                                'unit'              => $item->unit->full_name,
                                'unitDefaultAmount' => $item->unit->default_amount,
                                'diets'             => count($item?->category?->diets ?? []) ?
                                    implode('|', $item->category->diets->pluck('name')->toArray()) :
                                    '',
                            ]
                        ]
                    )
                ] :
                $data->map(
                    fn(Ingredient $item) => [
                        'tag_name'    => "[$item->id] $item->name",
                        'id'          => $item->id,
                        'custom_name' => null,
                    ]
                )
        );
    }

    /**
     * Perform search ingredients with ingredient tags by ajax from Select2.
     */
    public function searchClientDesignatedIngredients(
        Request                 $request,
        int                     $clientId,
        IngredientSearchService $service
    ): JsonResponse {
        try {
            $client = User::whereId($clientId)
                ->with('questionnaire', function (HasMany $relation) {
                    $relation
                        ->latest('id')
                        ->limit(1)
                        ->with(
                            'answers',
                            fn(HasMany $subRelation) => $subRelation->whereIn(
                                'questionnaire_question_id',
                                QuestionnaireQuestionIDEnum::userDietAndDiseases()
                            )
                        );
                })->firstOrFail();
            $answers = $client
                ?->questionnaire
                ->pluck('answers')
                ->map(fn(Collection $item) => $item->pluck('answer', 'questionnaire_question_id'))
                ->first()
                ?->toArray() ?? [];
            /**
             * We separate the diets from other answers because we need to pass them to the search service separately.
             */
            $diets = [];
            if ($answers !== []) {
                $diets = $answers[QuestionnaireQuestionIDEnum::DIETS->value];
                unset($answers[QuestionnaireQuestionIDEnum::DIETS->value]);
                $answers = Arr::flatten($answers);
            }
            // TODO: maybe add some cache to speed up the search
            return response()
                ->json(
                    new IngredientWithTagsSearchSelect2Resource(
                        $service->searchForIngredientsWithTags(
                            $answers,
                            $diets,
                            $request->q,
                            $request->user()?->lang ?? 'de'
                        )
                    )
                );
        } catch (ModelNotFoundException) {
            return response()->json(new IngredientWithTagsSearchSelect2Resource([]));
        }
    }
}

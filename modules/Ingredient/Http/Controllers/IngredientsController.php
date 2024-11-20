<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Controllers;

use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Modules\Ingredient\Http\Resources\IngredientWithTagsSearchSelect2Resource;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Services\IngredientSearchService;
use Throwable;

/**
 * Web Controller for ingredients.
 *
 * @package Modules\Ingredient\Http\Controllers
 */
final class IngredientsController extends Controller
{
    /**
     * Get all of available ingredients.
     */
    public function index(Request $request): Response|JsonResponse
    {
        return $request->expectsJson() ?
            response()->json(Ingredient::getAll()) :
            response(Ingredient::getAll());
    }

    /**
     * Get all ingredients without user excluded ingredients
     * TODO: check and rework for specific type of resource
     * @return Collection<array-key,Ingredient>
     */
    public function getUserAllowedIngredients(Request $request): Collection
    {
        return $request->user()->getAllowedIngredients();
    }

    /**
     * Search over all user available ingredients via Select2.
     *
     * @route GET /user/ingredients/search
     */
    public function searchIngredientsViaSelect2(Request $request): JsonResponse
    {
        $searchVal = $request->q;
        $query     = Ingredient::withOnly(['translations', 'unit'])->allowedForUser($request->user()->id);

        if (!is_null($searchVal)) {
            $query->whereTranslationLike('name', "%$searchVal%");
        }

        $data = $query->orderByTranslation('name')->get(['id']);

        // Format response for select2 format
        return response()->json(
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
                        ]
                    ]
                )
            ]
        );
    }

    /**
     * Perform search ingredients with ingredient tags by ajax from Select2.
     * TODO: repeating with admin...need to optimize
     * TODO: need to rework search mechanism
     * @note Supposed to be for new user...what to do about it?
     */
    public function searchUserDesignated(Request $request, IngredientSearchService $service): JsonResponse
    {
        /**@var User $user */
        $user = $request->user();
        try {
            $answers = $user
                ->load([
                    'questionnaire' => function (HasMany $relation) {
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
                    }
                ])
                ?->questionnaire
                ->pluck('answers')
                ->map(fn(DBCollection $item) => $item->pluck('answer', 'questionnaire_question_id'))
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
            // TODO: maybe add cache to speed up the search
            return response()
                ->json(
                    new IngredientWithTagsSearchSelect2Resource(
                        $service->searchForIngredientsWithTags(
                            $answers,
                            $diets,
                            $request->q,
                            $user->lang
                        )
                    )
                );
        } catch (Throwable) {
            return response()->json(new IngredientWithTagsSearchSelect2Resource([]));
        }
    }
}

<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Request as RequestFacade;
use Modules\Ingredient\Http\Requests\Admin\IngredientTagStoreRequest;
use Modules\Ingredient\Http\Requests\Admin\Tag\SearchIngredientTagRequest;
use Modules\Ingredient\Jobs\RecalculateUsersForbiddenIngredientsJob;
use Modules\Ingredient\Models\IngredientTag;

/**
 * Controller for NotificationType.
 *
 * @package Modules\Ingredient\Http\Controllers\Admin
 */
final class IngredientTagAdminController extends Controller
{
    public function store(IngredientTagStoreRequest $request): RedirectResponse
    {
        IngredientTag::updateOrCreate(['id' => $request->id], $request->validated())
            ->ingredients()
            ->sync($request->ingredients);
        RecalculateUsersForbiddenIngredientsJob::dispatch();
        $message = is_null($request->id) ? 'record_created_successfully' : 'record_updated_successfully';
        return redirect()
            ->back()
            ->with('success_message', trans('common.' . $message));
    }

    /**
     * @throws \Throwable
     */
    public function searchIngredientTag(SearchIngredientTagRequest $request): string
    {
        return view('admin::ingredient.tags.searchResult', [
            'ingredientTags' => IngredientTag::withCount('ingredients')
                ->searchBy(['search_name' => $request->search_name])
                ->paginate(20)
        ])->render();
    }

    /**
     * Search Ingredients by Select2 ajax request.
     */
    public function customSearch(): JsonResponse
    {
        $searchVal = RequestFacade::instance()->q;

        if (is_null($searchVal)) {
            return response()->json();
        }

        // If condition is true we suppose that user tries to find ingredient tag by title
        $query = (int)$searchVal === 0 ?
            IngredientTag::whereTranslationLike('title', "%$searchVal%") :
            IngredientTag::where('id', 'like', "%$searchVal%");


        // Format response for select2
        return response()->json(
            $query->get(['id'])->map(
                static fn(IngredientTag $item) => [
                    'tag_name'    => "[$item->id] $item->title",
                    'id'          => $item->id,
                    'custom_name' => null,
                ]
            )
        );
    }
}

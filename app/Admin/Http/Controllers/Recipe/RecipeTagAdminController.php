<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers\Recipe;

use App\Admin\Http\Requests\Recipe\Tag\RecipeTagStoreRequest;
use App\Admin\Http\Requests\Recipe\Tag\SearchRecipeTagRequest;
use App\Http\Controllers\Controller;
use App\Models\RecipeTag;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for NotificationType.
 *
 * @package App\Http\Controllers\Admin
 */
final class RecipeTagAdminController extends Controller
{
    public function store(RecipeTagStoreRequest $request): RedirectResponse
    {
        RecipeTag::updateOrCreate(['id' => $request->id], $request->validated())->recipes()->sync($request->recipes);
        $message = is_null($request->id) ? 'record_created_successfully' : 'record_updated_successfully';
        return redirect()
            ->back()
            ->with('success_message', trans('common.' . $message));
    }

    /**
     * Search for a recipe tag.
     */
    public function searchRecipeTag(SearchRecipeTagRequest $request): string
    {
        return view(
            'admin::recipe.tags.searchResult',
            [
                'recipeTags' => RecipeTag::withCount('recipes')
                    ->searchBy(['search_name' => $request->search_name])
                    ->paginate(20)
            ]
        )->render();
    }
}

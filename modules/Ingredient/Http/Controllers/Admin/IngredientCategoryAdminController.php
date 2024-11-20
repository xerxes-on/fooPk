<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateRecipeDiets;
use App\Services\{AdminStorage as AdminStorageService, SaveCategoryDiets};
use Illuminate\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\{RedirectResponse, Request};
use Modules\Ingredient\Http\Requests\Admin\IngredientCategoryFormRequest;
use Modules\Ingredient\Jobs\RecalculateUsersForbiddenIngredientsJob;
use Modules\Ingredient\Models\IngredientCategory;
use Modules\Internal\Enums\JobProcessingEnum;
use Modules\Internal\Models\{AdminStorage};

/**
 * Controller for ingredients categories.
 *
 * @package Modules\Ingredient\Http\Controllers\Admin
 */
final class IngredientCategoryAdminController extends Controller
{
    public function store(IngredientCategoryFormRequest $request): RedirectResponse
    {
        /** @var IngredientCategory $category */
        $category = IngredientCategory::updateOrCreate(
            ['id' => $request->id],
            ['name' => $request->name]
        );

        if (!$request->jobExists) {
            $parentId = is_null($request->mid_category) ?
                ($request->main_category !== $category->getKey() ? $request->main_category : null) :
                $request->mid_category;

            $treeInformation = [
                'main_category' => is_null($request->main_category) ? $category->getKey() : $request->main_category,
                'mid_category'  => is_null($request->mid_category) ? null : $request->mid_category
            ];

            $category->update(
                [
                    'parent_id'        => $parentId,
                    'tree_information' => $treeInformation
                ]
            );
        }

        $message  = is_null($request->id) ? 'record_created_successfully' : 'record_updated_successfully';
        $redirect = redirect()->back()->with('success_message', trans('common.' . $message));

        // save diets and prevent from going forward if error.
        $processSucceeded = $this
            ->processIngredientDietsSave(
                $category->diets()->pluck('diets.id')->toArray(),
                $request->diets,
                $category
            );
        if ($processSucceeded === false) {
            return $redirect;
        }

        $this->processIngredientsRecalculation($category->id);

        RecalculateUsersForbiddenIngredientsJob::dispatch();
        return $redirect;
    }

    /**
     * Validate and trigger ingredient diets saving.
     */
    private function processIngredientDietsSave(array $oldDiets, array $newDiets, IngredientCategory $category): bool
    {
        // Exit if resource is considered blocked
        if ((new AdminStorageService())->checkIfIngredientCategoryJobsExist($category->id)) {
            return false;
        }

        $diff = count($oldDiets) > count($newDiets) ? array_diff($oldDiets, $newDiets) : array_diff($newDiets, $oldDiets);

        // exit if nothing changed in diets
        if ($diff === []) {
            return false;
        }

        // Save diets to lower categories.
        (new SaveCategoryDiets($newDiets, $category))->handle();

        return true;
    }

    /**
     * Handle recipes Recalculating processes.
     *
     * @note Applicable to DB queue as per config. Not tested for queues in Redis and etc...
     */
    private function processIngredientsRecalculation(int $categoryId): void
    {
        try {
            $jobId = app(Dispatcher::class)->dispatch(new RecalculateRecipeDiets());
        } catch (\Throwable $e) {
            logError($e);
            return;
        }

        // Must be integer.
        if (!is_int($jobId)) {
            return;
        }

        AdminStorage::create(
            [
                'key'  => JobProcessingEnum::INGREDIENT_CATEGORY->value,
                'data' => ['category_id' => $categoryId, 'related_job' => $jobId]
            ]
        );
    }

    public function getChildCategories(Request $request): Collection
    {
        return IngredientCategory::whereParentId($request->id)->get();
    }
}

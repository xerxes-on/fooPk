<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers\Recipe;

use App\Admin\Http\Requests\Recipe\RecipeDistributionRequest;
use App\Http\Controllers\Controller;
use App\Models\RecipeDistribution;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for monthly recipe distribution.
 *
 * @package App\Http\Controllers\Admin
 */
final class RecipeDistributionAdminController extends Controller
{
    /**
     * Store an ingredient.
     */
    public function store(RecipeDistributionRequest $request): RedirectResponse
    {
        RecipeDistribution::updateOrCreate(['id' => $request->id], $request->validated());

        return redirect()
            ->back()
            ->with('success_message', trans('common.success'));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\MealtimeEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class CheckUserAccessToCustomRecipe
 *
 * @package App\Http\Middleware
 */
class CheckUserAccessToCustomRecipe
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $customRecipeId = $request->id;

        // From API request ID is not served, so we have to get it from the user's meals
        if ($customRecipeId === null) {
            $customRecipeId = $request->user()
                ->meals()
                ->whereDate('meal_date', $request->date)
                ->where('ingestion_id', MealtimeEnum::tryFromValue($request->ingestion)->value)
                ->limit(1)
                ->pluck('custom_recipe_id')
                ->first();
        }

        // if user doesn't have access -> redirect to 404
        if (is_null($customRecipeId) || in_array($request->user()
            ?->customRecipes()
            ->where(['id' => $customRecipeId])
            ->orWhere('recipe_id', $customRecipeId)
            ->doesntExist(), [true, null], true)) {
            abort(ResponseAlias::HTTP_NOT_FOUND);
        }

        return $next($request);
    }
}

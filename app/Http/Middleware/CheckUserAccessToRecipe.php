<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

/**
 * Class CheckUserAccessToRecipe
 *
 * @package App\Http\Middleware
 */
class CheckUserAccessToRecipe
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // check user access to recipe
        $recipe = \Auth::user()
            ->recipes()
            ->where('original_recipe_id', $request->id)
            ->first();

        // if user dont have access -> redirect to 404
        return is_null($recipe) ? abort(Response::HTTP_NOT_FOUND) : $next($request);
    }
}

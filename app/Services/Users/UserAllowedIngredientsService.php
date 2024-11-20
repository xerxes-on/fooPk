<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Ingredient\Models\Ingredient;

/**
 * Service to handle Users Ingredients.
 *
 * @package App\Services\Users
 */
final class UserAllowedIngredientsService
{
    /**
     * Get ingredients suitable for a user.
     *
     * @return Collection<array-key,Ingredient>
     */
    public function getAppropriate(User $user): Collection
    {
        // TODO: ,maybe add some cache? WARNING add a sub query
        return Ingredient::allowedForUser($user->id)
            ->orderByTranslation('name')
            ->get();
        /*$user->prohibitedIngredients()->pluck('id')->toArray()*/
        //        ->each(static function (Ingredient $ingredient) {
        // TODO: why do we need this?
        //        $ingredient->main_category = $ingredient->category->tree_information['main_category'];
        //    })
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\CacheKeys;
use App\Models\Seasons;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Repository for seasons.
 *
 * @package App\Repositories
 */
final class SeasonsRepository
{
    /**
     * Get all seasons relevant to a user.
     */
    public function getRelevant(User $user): Collection
    {
        $allUserRecipes = DB::table('user_recipe')->where('user_id', $user->id)->pluck('recipe_id')->toArray();
        return Seasons::whereHas(
            'ingredients.recipesAsStatic',
            function ($q) use ($allUserRecipes) {
                $q->whereIn('recipe_id', $allUserRecipes);
            }
        )
            ->orWhereHas(
                'ingredients.recipesAsVariable',
                function ($q) use ($allUserRecipes) {
                    $q->whereIn('recipe_id', $allUserRecipes);
                }
            )
            ->get();
    }

    /**
     * Get all seasons.
     */
    public function getAll(): Collection
    {
        $seasons = Cache::get(CacheKeys::seasons());

        if (!empty($seasons)) {
            return $seasons;
        }

        $seasons = Seasons::all();
        Cache::put(CacheKeys::seasons(), $seasons, config('cache.lifetime_day'));

        return $seasons;
    }
}

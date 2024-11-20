<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\RecipeProcessed;
use App\Models\{Ingestion, User, UserRecipe};
use Carbon\Carbon;

/**
 * Service for user meals.
 *
 * @package App\Services
 */
final class MealService
{
    /**
     * Toggle eat out status for a meal.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function skipMeal(User $user, Carbon $date, Ingestion $ingestion, int $eatOut): UserRecipe
    {
        $meal = $user
            ->meals()
//			->where('challenge_id', $user->subscription?->id)
            ->where('ingestion_id', $ingestion->id)
            ->whereDate('meal_date', $date)
            ->firstOrFail();

        $meal->eat_out = $eatOut;
        $meal->save();

        RecipeProcessed::dispatch();

        return $meal;
    }

}

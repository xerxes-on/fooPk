<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\Meal\PlannedDailyMealPreviewResource;
use App\Http\Resources\Meal\PlannedWeeklyMealPreviewResource;
use App\Models\User;
use Carbon\Carbon;

final class UserMealService
{
    /**
     * Get planned meals for a week.
     */
    public function getWeeklyMeals(User $user, Carbon $date): array
    {
        $meals = $user
            ->meals()
            ->with(['recipe' => ['complexity'], 'ingestion'])
            ->whereBetween('meal_date', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()])
            ->get();
        $previousWeek = $date->copy()->subWeek()->endOfWeek();
        $nextWeek     = $date->copy()->addWeek()->endOfWeek();
        return [
            'meals'    => PlannedWeeklyMealPreviewResource::collection($meals),
            'previous' => [
                'year' => $previousWeek->year,
                'week' => $previousWeek->weekOfYear,
            ],
            'next' => [
                'year' => $nextWeek->year,
                'week' => $nextWeek->weekOfYear,
            ],
        ];
    }

    /**
     * Get planned meals for a day.
     */
    public function getDailyMeals(User $user, Carbon $date): array
    {
        $meals = $user
            ->meals()
            ->with(['recipe' => ['complexity'], 'ingestion'])
            ->whereDate('meal_date', $date)
            ->get();

        $previousDay = $date->copy()->subDay();
        $nextDay     = $date->copy()->addDay();
        return [
            'meals'    => PlannedDailyMealPreviewResource::collection($meals),
            'previous' => [
                'year' => $previousDay->year,
                'week' => $previousDay->weekOfYear,
                'day'  => $previousDay->dayOfWeek,
            ],
            'next' => [
                'year' => $nextDay->year,
                'week' => $nextDay->weekOfYear,
                'day'  => $nextDay->dayOfWeek,
            ],
        ];
    }
}

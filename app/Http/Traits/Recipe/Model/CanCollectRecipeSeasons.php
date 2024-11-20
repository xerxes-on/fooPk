<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Ingredient\Models\Ingredient;

trait CanCollectRecipeSeasons
{
    public function collectRecipeSeasonsIds(): array
    {
        $seasonIds = collect(
            [
                $this->ingredients()->withOnly([
                    'seasons' => fn(BelongsToMany $relation) => $relation->setEagerLoads([])->select(['seasons.id'])
                ])->get(['ingredients.id']),
                $this->variableIngredients()->withOnly([
                    'seasons' => fn(BelongsToMany $relation) => $relation->setEagerLoads([])->select(['seasons.id'])
                ])->get(['ingredients.id'])
            ]
        )
            ->flatten()
            ->mapWithKeys(fn(Ingredient $ingredient) => [$ingredient->id => $ingredient->seasons->pluck('id')->toArray()])
            ->toArray();

        $commonValues = $seasonIds[key($seasonIds)] ?? [0];
        if (empty($commonValues)) {
            $commonValues = [0];
        }
        foreach ($seasonIds as $ids) {
            // If the nested array is empty, skip it
            if (empty($ids)) {
                continue;
            }

            if (in_array(0, $ids, true)) {
                continue;
            }


            // Find the common values between the existing common values and the current
            $intersection = array_intersect($commonValues, $ids);

            if ($intersection === [] && in_array(0, $commonValues, true)) {
                $commonValues = $ids;
                continue;
            }
            $commonValues = $intersection;
        }

        return $commonValues;
    }
}

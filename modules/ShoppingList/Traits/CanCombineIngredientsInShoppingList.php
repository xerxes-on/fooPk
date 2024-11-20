<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Traits;

use Illuminate\Support\Collection;

trait CanCombineIngredientsInShoppingList
{
    /**
     * Collection of ingredients extracted from recipes.
     */
    protected ?Collection $ingredients = null;

    /**
     * Group duplicated ingredients that were calculated for recipes.
     * Recipes may have the same ingredients and avoid duplications
     * we group them by id and merge their amount.
     */
    protected function combineCalculatedDuplicatedIngredients(): void
    {
        $this->ingredients = $this->getCombinedCalculatedDuplicatedIngredients($this->ingredients);
    }

    protected function getCombinedCalculatedDuplicatedIngredients(Collection $ingredients): Collection
    {
        return $ingredients
            ->flatten()
            ->groupBy('id')
            ->flatMap(
                function (Collection $items): Collection {
                    if ($items->count() === 1) {
                        return $items;
                    }
                    // TODO: NOTE how ingredients are collected when originated from different recipes.
                    $unique = $items->unique('id')->first();
                    // A collection must be returned, otherwise data can be skipped
                    return collect(
                        [
                            (object)[
                                'id'   => $unique->id,
                                'type' => $unique->type,
                                #TODO: sometimes can be fixed or variable
                                // Type can be fixed or variable even if $items belongs to different. This count by first and do not affect anything further
                                'amount' => $items->sum('amount')
                            ]
                        ]
                    );
                }
            );
    }

}

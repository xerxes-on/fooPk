<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\ShoppingList\Models\ShoppingList;

trait HasShoppingList
{
    /**
     * relation get purchase Lists
     */
    public function shoppingList(): HasOne
    {
        return $this->hasOne(ShoppingList::class);
    }

    /**
     * Scope a query for users` shopping list with ingredients and its category.
     */
    public function scopeShoppingListWithIngredientsAndCategory(): HasOne
    {
        return $this
            ->shoppingList()
            ->with(
                'ingredients',
                fn(HasMany $e) => $e->with(
                    [
                        'ingredient' => function (BelongsTo $qw) {
                            $qw->with([
                                'category' => static fn(HasOne $ew) => $ew->without(['diets']),
                                'unit'     => static fn(HasOne $ew) => $ew->setEagerLoads([]),
                                'translations',
                                'hint'
                            ]);
                        }
                    ]
                )
            );
    }
}

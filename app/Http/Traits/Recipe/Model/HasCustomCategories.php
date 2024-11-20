<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use App\Models\CustomRecipeCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasCustomCategories
{
    /**
     * Custom categories assigned to current recipe by all users.
     */
    public function allCustomCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            CustomRecipeCategory::class,
            'recipes_to_custom_categories',
            'recipe_id',
            'category_id',
        );
    }

    /**
     * Custom categories assigned to current recipe by a user.
     */
    public function customCategories(User $user): BelongsToMany
    {
        return $this->allCustomCategories()->where('user_id', $user->id);
    }
}

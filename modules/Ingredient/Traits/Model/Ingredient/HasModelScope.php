<?php

namespace Modules\Ingredient\Traits\Model\Ingredient;

use Illuminate\Database\Eloquent\Builder;

trait HasModelScope
{
    /**
     * Scope a query to only include ingredients with certain IDs.
     */
    public function scopeOfIds(Builder $query, array $ids): Builder
    {
        return $query->whereIntegerInRaw('id', $ids);
    }

    /**
     * Scope a query to only include ingredients allowed for a user.
     */
    public function scopeAllowedForUser(Builder $query, int $userId): Builder
    {
        return $query->whereNotIn('ingredients.id', static function (\Illuminate\Database\Query\Builder $query) use ($userId) {
            $query->select('ingredient_id')->from('user_prohibited_ingredients')->where('user_id', $userId);
        });
    }

    /**
     * Scope a query to only include ingredients by certain conditions.
     */
    public function scopeSearchBy(Builder $query, array $filters): Builder
    {
        // By name
        if (array_key_exists('search_name', $filters) && !empty($filters['search_name'])) {
            $query->whereHas(
                'translations',
                fn($query) => $query->where('name', 'LIKE', '%' . $filters['search_name'] . '%')
            );
        }

        // By category
        if (array_key_exists('category_id', $filters) && !empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // By tag
        if (
            array_key_exists('tags', $filters) &&
            !empty($filters['tags']) &&
            ctype_digit(implode('', $filters['tags']))
        ) {
            $query->whereHas(
                'tags',
                fn(Builder $q) => $q->whereIn('ingredient_tag_id', $filters['tags'])
            );
        }

        return $query;
    }
}

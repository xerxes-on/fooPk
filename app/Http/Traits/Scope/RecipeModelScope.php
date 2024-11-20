<?php

declare(strict_types=1);

namespace App\Http\Traits\Scope;

use App\Enums\Recipe\RecipeStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait RecipeModelScope
{
    /**
     * Scope for Search and filter recipes from page "All Recipes"
     */
    public function scopeSearchBy(Builder $query, ?array $conditions): Builder
    {
        if (empty($conditions)) {
            return $query;
        }

        # find by name or ingredients
        if (array_key_exists('search_name', $conditions) && $conditions['search_name'] != '') {
            $whereValue               = $conditions['search_name'];
            $transliteratedWhereValue = Str::replace(
                config('translatable.ascii_replace_map.de.from'),
                config('translatable.ascii_replace_map.de.to'),
                Str::ascii($whereValue, 'de')
            );
            $query->where(
                function ($subQuery) use ($whereValue, $transliteratedWhereValue) {
                    $subQuery->whereHas(
                        'ingredients',
                        function ($q) use ($whereValue, $transliteratedWhereValue) {
                            $q->whereTranslationLike('name', '%' . $whereValue . '%')
                                ->orWhereTranslationLike('name', '%' . $transliteratedWhereValue . '%');
                        }
                    )
                        ->orWhereHas(
                            'variableIngredients',
                            function ($q) use ($whereValue, $transliteratedWhereValue) {
                                $q->whereTranslationLike('name', '%' . $whereValue . '%')
                                    ->orWhereTranslationLike('name', '%' . $transliteratedWhereValue . '%');
                            }
                        )
                        ->orWhereTranslationLike('title', '%' . $whereValue . '%')
                        ->orWhere('recipes.id', $whereValue)
                        ->orWhereTranslationLike('title', '%' . $transliteratedWhereValue . '%');
                }
            );
        }

        # find by status
        if (array_key_exists('status', $conditions) && is_numeric($conditions['status'])) {
            $query->where('status', $conditions['status']);
        }

        # find by translations_done flag
        if (array_key_exists('translations_done', $conditions) && is_numeric($conditions['translations_done'])) {
            $query->where('translations_done', $conditions['translations_done']);
        }

        # find by ingestion
        if (array_key_exists('ingestion', $conditions) && !empty($conditions['ingestion'])) {
            $whereValue = $conditions['ingestion'];
            $query->whereHas(
                'ingestions',
                function ($q) use ($whereValue) {
                    $q->where('ingestion_id', $whereValue);
                }
            );
        }

        # find by complexity
        if (array_key_exists('complexity', $conditions) && !empty($conditions['complexity'])) {
            $query->where('complexity_id', $conditions['complexity']);
        }

        # find by cost
        if (array_key_exists('cost', $conditions) && !empty($conditions['cost'])) {
            $query->where('price_id', $conditions['cost']);
        }

        # find by diet
        if (array_key_exists('diet', $conditions) && !empty($conditions['diet'])) {
            $whereValue = $conditions['diet'];
            $query->whereHas(
                'diets',
                function ($q) use ($whereValue) {
                    $q->where('diet_id', $whereValue);
                }
            );
        }

        # find by ingredient
        if (array_key_exists('ingredients', $conditions) &&
            !is_null($conditions['ingredients']) &&
            ctype_digit(implode('', $conditions['ingredients']))
        ) {
            $ingredientIds = $conditions['ingredients'];

            $query->whereHas('ingredients', function ($q) use ($ingredientIds) {
                $q->whereIn('ingredient_id', $ingredientIds);
            });
        }

        # find by tag
        if (array_key_exists('recipe_tags', $conditions) &&
            !is_null($conditions['recipe_tags']) &&
            ctype_digit(implode('', $conditions['recipe_tags']))
        ) {
            $recipeTags = $conditions['recipe_tags'];

            $query->whereHas('tags', function ($q) use ($recipeTags) {
                $q->whereIn('recipe_tag_id', $recipeTags);
            });
        }

        # find by variable ingredient
        if (array_key_exists('variable_ingredients', $conditions) &&
            !is_null($conditions['variable_ingredients']) &&
            ctype_digit(implode('', $conditions['variable_ingredients']))
        ) {
            $variableIngredients = $conditions['variable_ingredients'];

            $query->whereHas('variableIngredients', function ($q) use ($variableIngredients) {
                $q->whereIn('ingredient_id', $variableIngredients);
            });
        }


        # find by favorite
        if (array_key_exists('favorite', $conditions) && is_array($conditions['favorite'])) {
            $query->whereIn('recipes.id', $conditions['favorite']);
        }

        # find by validity
        if (array_key_exists('invalid', $conditions) && $conditions['invalid'] >= 0) {
            $query->where('user_recipe_calculated.invalid', $conditions['invalid']);
        }

        # find by season
        if (!empty($conditions['seasons']) && is_numeric($conditions['seasons']) && $conditions['seasons'] > 0) {
            $query->whereHas('seasons', static fn(Builder $seasons) => $seasons->where('seasons_id', $conditions['seasons']));
        }

        // find by custom category
        if (array_key_exists('custom_category', $conditions) && !empty($conditions['custom_category'])) {
            $whereValue = $conditions['custom_category'];
            $query->whereHas(
                'allCustomCategories',
                fn(Builder $q) => $q->where('category_id', $whereValue)->where('user_id', Auth::user()->id)
            );
        }

        // find by recipe tag
        if (array_key_exists('recipe_tag', $conditions) && !empty($conditions['recipe_tag'])) {
            $whereValue = $conditions['recipe_tag'];
            $query->whereHas('tags', fn(Builder $q) => $q->where('recipe_tag_id', $whereValue));
        }

        return $query;
    }

    public function scopeIsDraft(Builder $query): Builder
    {
        return $query->where('status', RecipeStatusEnum::DRAFT->value);
    }

    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where('status', RecipeStatusEnum::ACTIVE->value);
    }

    public function scopeIsOutdated(Builder $query): Builder
    {
        return $query->where('status', RecipeStatusEnum::OUTDATED->value);
    }
}

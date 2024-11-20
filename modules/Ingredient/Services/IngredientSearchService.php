<?php

declare(strict_types=1);

namespace Modules\Ingredient\Services;

use App\Models\Allergy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;
use Modules\Ingredient\Models\IngredientTag;

/**
 * Service for searching ingredients.
 *
 * @package Modules\Ingredient\Services
 */
final class IngredientSearchService
{
    /**
     * Search ingredients with its tags.
     *
     * TODO: need to check the following
     * - Should spices be excluded from search
     * - How can we take into account user diets
     * - Cache invalidation....how and should we do it?
     */
    public function searchForIngredientsWithTags(
        array  $userHealthConditions,
        array  $userDiets,
        string $searchVal,
        string $lang = 'de'
    ): array {
        $excludedCategories = $this->prepareExcludedCategories($userHealthConditions, $userDiets);
        return [
            'ingredients' => Ingredient::withOnly('translations')
                ->whereIntegerNotInRaw('category_id', $excludedCategories)
                ->whereTranslationLike('name', "%$searchVal%", $lang)
                ->get(['id', 'category_id'])
                ->map(fn(Ingredient $item): array => [
                    'id'   => $item->id,
                    'text' => $item->translations->where('locale', $lang)->first()->name,
                ])
                ->toArray(),
            'tags' => IngredientTag::searchBy(['search_name' => $searchVal], $lang, false)
                ->with(['translations', 'ingredients:id'])
                ->get('id')
                ->map(
                    fn(IngredientTag $tag): array => [
                        'id'   => $tag->id . '_group',
                        'text' => sprintf(
                            '%s (%s)',
                            $tag->translations->where('locale', $lang)->first()->title,
                            trans('common.all', locale: $lang)
                        ),
                        'ingredients' => $tag
                            ->ingredients
                            ->map(fn(Ingredient $item): array => [
                                'id'   => $item->id,
                                'text' => $item->translations->where('locale', $lang)->first()?->name ?? $item?->name,
                            ])
                            ->toArray()
                    ]
                )
                ->toArray()
        ];
    }

    /**
     * Prepare and Cache excluded categories for specific health conditions.
     */
    private function prepareExcludedCategories(array $userHealthConditions, array $userDiets): array
    {
        $cacheKey = generate_cache_key($userHealthConditions);

        $excludedCategories = Cache::get($cacheKey, []);
        if ($excludedCategories === []) {
            /** Categories can be collected via Allergy model, it relates to both allergies & diseases*/
            if (!empty($userHealthConditions)) {
                // `other` value will be just ignored
                Allergy::withOnly(
                    [
                        'ingredientCategories' => fn(BelongsToMany $q) => $q->without(['translations', 'diets'])->select(['id', 'parent_id'])
                    ]
                )
                    ->whereIn('slug', $userHealthConditions)
                    ->get()
                    ->each(function (Allergy $allergy) use (&$excludedCategories) {
                        $allergy?->ingredientCategories?->each(
                            function (IngredientCategory $category) use (&$excludedCategories) {
                                $excludedCategories[] = $category->id;
                            }
                        );
                    });
            }

            $excludedCategories = array_unique(array_map('intval', $excludedCategories), SORT_NUMERIC);

            // recursive generating all child categories from the excluded list
            foreach ($excludedCategories as $categoryID) {
                $query = "SELECT `id`
                FROM (SELECT `id`,`parent_id` FROM `ingredient_categories`
                         ORDER BY `parent_id`, `id`) `ingredient_categories`,
                        (SELECT @pv := '$categoryID') initialisation
                WHERE find_in_set(parent_id, @pv) > 0 AND @pv := concat(@pv, ',', id)";
                $childrenIds = \DB::select($query);

                if (!empty($childrenIds) && is_array($childrenIds)) {
                    $childrenIds        = array_map(fn($item) => $item->id, $childrenIds);
                    $excludedCategories = array_merge($excludedCategories, $childrenIds);
                }
            }

            $excludedCategories = array_unique(array_map('intval', $excludedCategories), SORT_NUMERIC);
            sort($excludedCategories);
            Cache::set($cacheKey, $excludedCategories, config('cache.lifetime_short'));
        }

        // TODO: what about diets

        return $excludedCategories;
    }
}

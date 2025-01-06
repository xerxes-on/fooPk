<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Services;

use App\Models\{User};
use Illuminate\Support\Collection;
use Modules\Ingredient\Models\IngredientCategory;
use Modules\Ingredient\Models\IngredientUnit;
use Modules\Ingredient\Services\IngredientConversionService;
use Modules\ShoppingList\Models\ShoppingList;
use Modules\ShoppingList\Models\ShoppingListIngredient;

/**
 * Service to retrieve shopping list data.
 *
 * @package App\Services\ShoppingList
 */
final class ShoppingListRetrieverService
{
    private string $baseSlug = 'category_';

    /**
     * Get users' shopping list.
     */
    public function getList(User $user, bool $includeRecipes = true): array
    {
        /**
         * Get active list or create new
         */
        $list = $user->shoppingListWithIngredientsAndCategory()->firstOrCreate();

        $response = [
            'list'                  => $list,
            'ingredient_categories' => $this->generateListData($user->lang, $list->ingredients),
        ];

        if ($includeRecipes) {
            $response['recipes'] = $this->getAllRecipes($list, $user->id);
        }

        return $response;
    }

    /**
     * Generate purchase list array with category and ingredients information
     */
    public function generateListData(string $locale, Collection $ingredients): array
    {
        if ($ingredients->isEmpty()) {
            return [];
        }

        $categoryCollection   = $this->getIngredientsCategory($ingredients);
        $ingredientCategories = [];

        $ingredients->each(function (ShoppingListIngredient $ingredient) use ($locale, $categoryCollection, &$ingredientCategories) {
            $slug         = $this->baseSlug;
            $categoryName = trans('common.other');
            $categoryId   = null;

            // Preparing category name and id
            if (!is_null($ingredient->category_id)) {
                // Mid_category most time gets null, need to condition this matter
                $category = is_null($ingredient?->ingredient?->category?->tree_information['mid_category']) ?
                    null :
                    $categoryCollection
                        ->where('id', $ingredient->ingredient->category->tree_information['mid_category'])
                        ->first();

                if (!is_null($ingredient->category_id) && !is_null($category)) {
                    $categoryName = $category->name;
                    $categoryId   = $category->id;
                    $slug .= $category->id;
                } elseif (is_null($category)) {
                    $categoryName = $ingredient->category->name;
                    $categoryId   = $ingredient->category_id;
                    $slug .= $ingredient->category_id;
                }
            }

            // Merge ingredients of the same category
            if (!isset($ingredientCategories[$slug])) {
                $ingredientCategories[$slug] = [
                    'category' => [
                        'id'   => $categoryId,
                        'name' => $categoryName,
                    ],
                    'ingredients' => [],
                ];
            }

            // check custom ingredients
            if (is_null($ingredient->ingredient_id)) {
                $ingredientCategories[$slug]['ingredients'][] = [
                    'id'           => $ingredient->id,
                    'custom_title' => $ingredient->custom_title,
                    'completed'    => $ingredient->completed,
                ];
                return;
            }

            // get default ingredient values
            $amount = $ingredient->amount;
            $unit   = $ingredient->ingredient->unit;

            // check conversion (1000 g. => 1 kg.)
            if (!is_null($unit->next_unit_id) && $amount >= $unit->max_value) {
                $amount /= $unit->max_value;
                $unit = IngredientUnit::with('translations')->find($unit->next_unit_id);
            }
            $unit->loadMissing('translations');

            $ingredientCategories[$slug]['ingredients'][] = [
                'id'                             => $ingredient->id,
                'ingredient_id'                  => $ingredient->ingredient_id,
                'name'                           => $ingredient->ingredient->name,
                'amount'                         => $amount,
                'unit'                           => $unit->visibility ? $unit->short_name : '',
                'completed'                      => $ingredient->completed,
                'hint'                           => $this->getIngredientHintContent($ingredient, $locale),
                IngredientConversionService::KEY => app(IngredientConversionService::class)->generateData($ingredient->ingredient, $amount)
            ];
        });

        $this->sortIngredientCategories($ingredientCategories);

        return $ingredientCategories;
    }

    private function getIngredientsCategory(Collection $ingredients): Collection
    {
        // Map ingredients removing ones without mid_category
        $usedIngredientCategoryIds = $ingredients
            ->map(
                static fn(ShoppingListIngredient $item) => $item?->ingredient?->category?->tree_information['mid_category'] ?? null
            )
            ->filter(
                static function (?int $ingredient) {
                    if (!is_null($ingredient)) {
                        return $ingredient;
                    }
                }
            )
            ->toArray();

        return IngredientCategory::without(['diets'])
            ->whereIntegerInRaw('id', $usedIngredientCategoryIds)
            ->get();
    }

    private function getIngredientHintContent(ShoppingListIngredient $ingredient, string $locale): array
    {
        //check for hints
        $hint = $ingredient->ingredient?->hint?->translations->where('locale', $locale)->first();
        if ($hint === null) {
            return [];
        }
        return [
            'title'     => $ingredient->ingredient->name,
            'content'   => $hint->content,
            'link_url'  => $hint->link_url,
            'link_text' => $hint->link_text,
        ];
    }

    /**
     * Retrieve all recipes from specific list.
     *
     * TODO: maybe implement cache here. Must be aware of cache correct cache invalidation.
     */
    private function getAllRecipes(ShoppingList $list, int $userId): Collection
    {
        /**
         * Merge recipes collections with custom and flexmeals proceed with single collection.
         * Sql order/group will not work as we need sorted collection of various models.
         * @note relations are loaded correctly. Leave as is.
         */
        return collect(
            [
                $list->recipes()->get(),
                $list->customRecipes()->with('ingestion')->get(),
                $list->flexmeals($userId)->with('ingestion')->get()
            ]
        )
            ->flatten()
            ->sortBy(['pivot.mealtime'], SORT_NUMERIC)
            ->groupBy('pivot.meal_day', true)
            ->sortKeysUsing(fn(string $a, string $b) => strtotime($a) - strtotime($b));
    }

    /**
     * Resort categories as per required order.
     */
    private function sortIngredientCategories(array &$ingredientCategories): void
    {
        $map = config('shopping-list.category_sorting_order_map');
        uksort($ingredientCategories, function (string $firstKey, string $secondKey) use ($map) {
            if (in_array($this->baseSlug, [$firstKey, $secondKey], true)) {
                return -1;
            }

            $index1 = array_search((int)preg_replace('/\D/', '', $firstKey), $map, true);
            $index2 = array_search((int)preg_replace('/\D/', '', $secondKey), $map, true);

            if ($index1 === $index2) {
                return 0;
            }
            return ($index1 < $index2) ? -1 : 1;
        });
    }
}

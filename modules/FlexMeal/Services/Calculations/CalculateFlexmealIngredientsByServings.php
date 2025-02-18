<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Services\Calculations;

use Modules\FlexMeal\Models\FlexmealLists;
use Modules\Ingredient\Enums\IngredientTypeEnum;
use Modules\Ingredient\Http\Resources\IngredientHintResource;
use Modules\Ingredient\Services\IngredientConversionService;

final class CalculateFlexmealIngredientsByServings
{
    public function __invoke(FlexmealLists $flexmeal, int $servings): array
    {
        $_parseData = [];
        foreach ($flexmeal->ingredients as $flexMealIngredient) {
            $baseAmount       = $flexMealIngredient->getOriginal('amount');
            $calculatedAmount = (int) round($baseAmount * $servings);

            $ingredient = $flexMealIngredient->ingredient;
            if (!$ingredient) {
                continue;
            }
            $prepareData = [
                'ingredient_id'     => $ingredient->id,
                'ingredient_type'   => IngredientTypeEnum::FIXED->value,
                'main_category'     => $ingredient->category->tree_information['main_category'],
                'ingredient_amount' => $calculatedAmount,
                'ingredient_text'   => $ingredient->unit->visibility
                    ? "{$ingredient->unit->short_name} {$ingredient->name}"
                    : $ingredient->name,
                'ingredient_name'                => $ingredient->name,
                'ingredient_unit'                => $ingredient->unit->visibility ? $ingredient->unit->short_name : '',
                'allow_replacement'              => false,
                'hint'                           => new IngredientHintResource($ingredient),
                IngredientConversionService::KEY => app(IngredientConversionService::class)
                    ->generateData($ingredient, $calculatedAmount)
            ];

            $_parseData[] = $prepareData;
        }

        return $_parseData;
    }

}

<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Services\Calculations;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;
use Modules\FlexMeal\Models\Flexmeal;
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\Ingredient\Enums\IngredientTypeEnum;
use Modules\Ingredient\Http\Resources\IngredientHintResource;
use Modules\Ingredient\Services\IngredientConversionService;

/**
 * Class responsible for calculating nutrients in Flexmeals.
 *
 * @package App\Services\Calculations
 */
final class FlexMealCalculator
{
    /**
     * Collection of used nutrients
     * @var \Illuminate\Support\Collection|null
     */
    private ?SupportCollection $nutrientsUsed;

    /**
     * Total amount of used carbohydrates.
     * @var float
     */
    private float $carbohydrates = 0;

    /**
     * Total amount of used fats.
     * @var float
     */
    private float $fats = 0;

    /**
     * Total amount of used proteins.
     * @var float
     */
    private float $proteins = 0;

    /**
     * Total amount of used calories.
     * @var float
     */
    private float $calories = 0;


    /**
     * Instance handler.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection $flexMealsCollection
     *
     * @return \Illuminate\Support\Collection
     */
    public function __invoke(EloquentCollection|SupportCollection $flexMealsCollection): SupportCollection
    {
        $this->extractNutrients($flexMealsCollection);
        $this->calculateNutrients();
        $this->calculateCalories();
        return collect(
            [
                'calories'      => $this->calories,
                'carbohydrates' => $this->carbohydrates,
                'fats'          => $this->fats,
                'proteins'      => $this->proteins,
            ]
        );
    }

    /**
     * Extract nutrients from flexmeal.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection $flexMealsCollection
     */
    private function extractNutrients(EloquentCollection|SupportCollection $flexMealsCollection): void
    {
        $this->nutrientsUsed = $flexMealsCollection->map(
            function (Flexmeal $flexMeal) {
                return collect(
                    [
                        'amount'        => $flexMeal?->amount,
                        'defaultAmount' => $flexMeal?->ingredient?->unit->default_amount,
                        'calories'      => $flexMeal?->ingredient?->calories,
                        'carbohydrates' => $flexMeal?->ingredient?->carbohydrates,
                        'fats'          => $flexMeal?->ingredient?->fats,
                        'proteins'      => $flexMeal?->ingredient?->proteins,
                    ]
                );
            }
        );
    }

    /**
     * Calculates total nutrients.
     */
    private function calculateNutrients(): void
    {
        $this->carbohydrates = round(
            $this->nutrientsUsed->sum(
                fn($item) => $item['carbohydrates'] / $item['defaultAmount'] * $item['amount']
            ),
            2
        );
        $this->fats = round(
            $this->nutrientsUsed->sum(
                fn($item) => $item['fats'] / $item['defaultAmount'] * $item['amount']
            ),
            2
        );
        $this->proteins = round(
            $this->nutrientsUsed->sum(
                fn($item) => $item['proteins'] / $item['defaultAmount'] * $item['amount']
            ),
            2
        );
    }

    /**
     * Calculate total calories according to specified formula.
     * fat grams * 9 plus carb grams * 4 + protein grams * 4
     */
    private function calculateCalories(): void
    {
        $this->calories = round($this->fats * 9 + $this->carbohydrates * 4 + $this->proteins * 4, 2);
    }

    public static function parseIngredients(FlexmealLists $flexmeal, int $servings): array
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

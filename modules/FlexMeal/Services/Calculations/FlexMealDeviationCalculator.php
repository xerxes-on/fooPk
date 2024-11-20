<?php

namespace Modules\FlexMeal\Services\Calculations;

use Modules\FlexMeal\Models\Flexmeal;
use Modules\FlexMeal\Models\FlexmealLists;

/**
 * Service allowing to calculate deviation between planned and actual nutrients in a flexmeal.
 *
 * @package App\Services\FlexMeal\Calculations
 */
class FlexMealDeviationCalculator
{
    private array $dietdata;

    private array $calculatedNutrients;

    private array $errors = [];

    private array $allowedDeviation = [
        'calories'      => 100,
        'carbohydrates' => 15,
        'fats'          => 10,
        'proteins'      => 15
    ];

    /**
     * Main method to calculate deviation between planned and actual nutrients in a flexmeal.
     */
    public function calculate(FlexmealLists $flexmeal, string $mealTime): array
    {
        return $this->setDietData($mealTime)
            ->setCalculatedNutrients($flexmeal->id)
            ->findCaloriesDeviation()
            ->findCarbohydratesDeviation()
            ->findFatsDeviation()
            ->findProteinsDeviation()
            ->generateReport();
    }

    /**
     * Diet data setter.
     */
    private function setDietData(string $mealTime): static
    {
        $this->dietdata = auth()->user()->dietdata['ingestion'][$mealTime] ?? [
            'Kcal' => 0,
            'KH'   => 0,
            'EW'   => 0,
            'F'    => 0
        ];
        return $this;
    }

    /**
     * Calculated nutrients setter.
     */
    private function setCalculatedNutrients(int $flexmealId): static
    {
        $ingredientsUsed = Flexmeal::whereListId($flexmealId)->with('ingredient')->get();

        $values = collect();

        foreach ($ingredientsUsed as $item) {
            if ($item->list_id !== $flexmealId) {
                continue;
            }

            $values->push($item);
        }

        $this->calculatedNutrients = (new FlexMealCalculator())($values)->toArray();
        return $this;
    }

    /**
     * Calories deviation finder.
     */
    private function findCaloriesDeviation(): static
    {
        $value = abs($this->calculatedNutrients['calories'] - $this->dietdata['Kcal']);
        if ($value >= $this->allowedDeviation['calories']) {
            $this->errors['calories'] = [
                'planned'              => $this->dietdata['Kcal'],
                'actual'               => $this->calculatedNutrients['calories'],
                'deviation_percentage' => $this->formatDeviation($value)
            ];
        }
        return $this;
    }

    /**
     * Carbohydrates deviation finder.
     */
    private function findCarbohydratesDeviation(): static
    {
        $value = $this->getPercentageChange($this->calculatedNutrients['carbohydrates'], $this->dietdata['KH']);
        if ($value >= $this->allowedDeviation['carbohydrates']) {
            $this->errors['carbohydrates'] = [
                'planned'              => $this->dietdata['KH'],
                'actual'               => $this->calculatedNutrients['carbohydrates'],
                'deviation_percentage' => $this->formatDeviation($value)
            ];
        }
        return $this;
    }

    /**
     * Fats deviation finder.
     */
    private function findFatsDeviation(): static
    {
        $value = $this->getPercentageChange($this->calculatedNutrients['fats'], $this->dietdata['F']);
        if ($value >= $this->allowedDeviation['fats']) {
            $this->errors['fats'] = [
                'planned'              => $this->dietdata['F'],
                'actual'               => $this->calculatedNutrients['fats'],
                'deviation_percentage' => $this->formatDeviation($value)
            ];
        }
        return $this;
    }

    /**
     * Proteins deviation finder.
     */
    private function findProteinsDeviation(): static
    {
        $value = $this->getPercentageChange($this->calculatedNutrients['proteins'], $this->dietdata['EW']);
        if ($value >= $this->allowedDeviation['proteins']) {
            $this->errors['proteins'] = [
                'planned'              => $this->dietdata['EW'],
                'actual'               => $this->calculatedNutrients['proteins'],
                'deviation_percentage' => $this->formatDeviation($value)
            ];
        }
        return $this;
    }

    /**
     * Deviation report generator.
     */
    private function generateReport(): array
    {
        $response = [
            'success' => false,
            'data'    => null,
            'message' => trans('common.flexmeal_deviation.error.message'),
            'errors'  => $this->errors,
        ];

        if (empty($this->errors)) {
            $response['success'] = true;
            $response['message'] = trans('common.success');
        }

        return $response;
    }

    /**
     * Helper to calculate percentage change between two values.
     */
    private function getPercentageChange(int|float $first, int|float $second): float|int
    {
        // Prevent division by zero
        $first  = max($first, 0.1);
        $second = max($second, 0.1);

        return abs(($first - $second) / $first) * 100;
    }

    /**
     * Format deviation percentage number.
     */
    private function formatDeviation(float $value): float
    {
        return round($value, 2);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Ingredient\Services;

use BadMethodCallException;
use Modules\Ingredient\Enums\IngredientAlternativeUnit;
use Modules\Ingredient\Exceptions\NonConvertableIngredient;
use Modules\Ingredient\Models\Ingredient;

/**
 * Service for generating conversion data for ingredients.
 *
 * This service is used to generate conversion data for ingredients.
 * It also provides methods for calculating conversion amount based on specific business requirements.
 *
 * @package Modules\Ingredient\Services
 */
final class IngredientConversionService
{
    /**
     * Array data key. Used to determine the key for conversion data in resources.
     */
    public const KEY = 'conversion_data';

    /**
     * Map of fractions to their unicode representation.
    */
    private const FRACTION_MAP = [
        '0.25' => '¼',
        '0.5'  => '½',
        '0.75' => '¾',
    ];

    public function generateData(Ingredient $ingredient, int|float $ingredientAmount): array
    {
        $ingredient->loadMissing('alternativeUnit');
        $data = [];
        if ($ingredient->alternative_unit_id !== null && ($ingredientAmount > 0 && $ingredient->unit_amount > 0)) {
            try {
                $data = [
                    'ingredient_plural_name'       => $ingredient->name_plural,
                    'ingredient_piece'             => $ingredient->unit_amount,
                    'ingredient_alternative_unit'  => $this->getIngredientUnitName($ingredient),
                    'ingredient_conversion_amount' => $this->getConversionAmount($ingredient, $ingredientAmount),
                    'fraction_map'                 => self::FRACTION_MAP,
                ];

            } catch (NonConvertableIngredient) {
                // Do nothing, as this ingredient is not convertable
            } catch (\Throwable $e) {
                logError($e);
            }
        }
        return $data;
    }

    private function getIngredientUnitName(Ingredient $ingredient): string
    {
        return $ingredient->alternativeUnit->visibility ? $ingredient->alternativeUnit->short_name : '';
    }

    /**
     * @throws NonConvertableIngredient
     */
    private function getConversionAmount(Ingredient $ingredient, int|float $ingredientAmount): float|int
    {
        $conversionValue = $ingredientAmount / $ingredient->unit_amount;
        $unit            = IngredientAlternativeUnit::tryFrom($ingredient->alternative_unit_id);
        if (is_null($unit)) {
            return round($conversionValue, 1);
        }

        return match ($unit) {
            IngredientAlternativeUnit::TEASPOON,
            IngredientAlternativeUnit::TABLESPOON,
            IngredientAlternativeUnit::SLICES => $this->applySimpleRounding($conversionValue),
            IngredientAlternativeUnit::PIECES => $this->applyRoundingForPieces($ingredient, $conversionValue),
            IngredientAlternativeUnit::PACKAGES,
            IngredientAlternativeUnit::TUBS,
            IngredientAlternativeUnit::CANS => $this->applyComplexRounding($conversionValue),
        };
    }

    /**
     * @throws NonConvertableIngredient
     */
    private function applyRoundingForPieces(Ingredient $ingredient, int|float $conversionValue): float|int
    {
        if ($ingredient->unit_amount < 60) {
            return $this->applySimpleRounding($conversionValue);
        }

        if ($ingredient->unit_amount >= 60 && $ingredient->unit_amount < 200) {
            if ($conversionValue < 0.25) {
                throw new NonConvertableIngredient();
            }
            $fraction       = $this->calcFraction($conversionValue);
            $roundingMethod = (($fraction > 0.25 && $fraction < 0.5) || ($fraction > 0.75)) ? 'ceil' : 'floor';

            return $this->roundToNearest($conversionValue, 0.25, $roundingMethod);
        }

        // when unit amount is >= 200
        return $this->applyComplexRounding($conversionValue);
    }

    /**
     * @throws NonConvertableIngredient
     */
    private function applySimpleRounding(int|float $conversionValue): float|int
    {
        if ($conversionValue < 0.5) {
            throw new NonConvertableIngredient('Conversion value is lower then 0.5');
        }
        $roundingMethod = $this->calcFraction($conversionValue) >= 0.5 ? 'ceil' : 'floor';
        return $this->roundToNearest($conversionValue, 0.5, $roundingMethod);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) - this method is complex due to business logic
     * @throws NonConvertableIngredient
     */
    private function applyComplexRounding(int|float $conversionValue): float|int
    {
        if ($conversionValue < 0.2) {
            throw new NonConvertableIngredient('Conversion value is lower then 0.2');
        }

        if ($conversionValue >= 0.2 && $conversionValue < 0.25) {
            return $this->roundToNearest($conversionValue, 0.25, 'ceil');
        }

        $fraction = $this->calcFraction($conversionValue);

        if ($conversionValue < 2) {
            $roundTo        = 0.25;
            $roundingMethod = (($fraction > 0.375 && $fraction < 0.5) || ($fraction > 0.625 && $fraction < 0.75) || ($fraction > 0.875)) ?
                'ceil' :
                'floor';
        } else {
            $roundTo        = 0.5;
            $roundingMethod = (($fraction > 0.25 && $fraction < 0.5) || ($fraction > 0.75)) ? 'ceil' : 'floor';
        }

        return $this->roundToNearest($conversionValue, $roundTo, $roundingMethod);
    }

    private function roundToNearest(int|float $number, int|float $roundTo, string $roundingMethod): float|int
    {
        if (!function_exists($roundingMethod)) {
            throw new BadMethodCallException("Rounding method `$roundingMethod` does not exist");
        }

        return $roundingMethod($number / $roundTo) * $roundTo;
    }

    private function calcFraction(int|float $number): float|int
    {
        return $number - floor($number);
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of calculated ingredient data.
 */
class CalculatedIngredient extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // manually selecting proper translations
        $locale = app()->getLocale();

        foreach ($this['unit']['translations'] as $unitTranslation) {
            $selectedUnitTranslation = $unitTranslation['short_name'];

            if ($unitTranslation['locale'] == $locale) {
                break;
            }
        }

        foreach ($this['translations'] as $ingredientTranslation) {
            $selectedIngredientTranslation = $ingredientTranslation['name'];

            if ($ingredientTranslation['locale'] == $locale) {
                break;
            }
        }

        return [
            'ingredient_id' => $this['id'],
            // currently the resource is used only for variable ingredients
            // if it changes, following line should be changed as well
            'ingredient_type'   => 'variable',
            'main_category'     => $this['category']['tree_information']['main_category'],
            'ingredient_amount' => (int)$this['amount'],
            'unit'              => $selectedUnitTranslation,
            'ingredient_name'   => $selectedIngredientTranslation,
        ];
    }
}

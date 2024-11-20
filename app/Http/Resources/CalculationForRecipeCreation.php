<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of calculations before custom recipe creation.
 */
class CalculationForRecipeCreation extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'calculated-ingredients' => CalculatedIngredient::collection($this['ingradients_variable']),
            'additional-info'        => [
                'calculated_KCal' => $this['calculated_KCal'],
                'calculated_KH'   => $this['calculated_KH'],
                'calculated_EW'   => $this['calculated_EW'],
                'calculated_F'    => $this['calculated_F'],
            ],
        ];
    }
}

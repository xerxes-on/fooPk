<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Users nutrition data to be added to a recipe or a meal.
 */
class UsersNutritionData extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $dietData = $this->dietdata;
        return [
            'ew_percents' => $dietData['ew_percents'] ?? null,
            'kh_percents' => $dietData['kh_percents'] ?? null,
            'f_percents'  => $dietData['f_percents'] ?? null,
        ];
    }
}

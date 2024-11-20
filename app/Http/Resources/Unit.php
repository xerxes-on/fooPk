<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a unit of measurement.
 */
class Unit extends JsonResource
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
            'id'             => $this->id,
            'full_name'      => $this->full_name,
            'short_name'     => $this->short_name,
            'default_amount' => $this->default_amount,
            'max_value'      => $this->max_value
        ];
    }
}

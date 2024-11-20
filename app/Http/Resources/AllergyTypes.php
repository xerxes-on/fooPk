<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AllergyTypes
 *
 * @property-read \App\Models\AllergyTypes $id
 * @property \App\Models\AllergyTypes $name
 * @property-read \App\Models\Allergy[]|\Illuminate\Database\Eloquent\Collection $allergies
 * @package App\Http\Resources
 */
class AllergyTypes extends JsonResource
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
            'id'        => $this->id,
            'name'      => $this->name,
            'allergies' => Allergy::collection($this->allergies),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Allergy
 *
 * @property-read string|int $id
 * @property-read string $slug
 * @property-read string $name
 *
 * @package App\Http\Resources
 */
class Allergy extends JsonResource
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
            'id'      => $this->id,
            'slug'    => $this->slug,
            'name'    => $this->name,
            'tooltip' => trans_fb("survey_questions.{$this->slug}_tooltip")
        ];
    }
}

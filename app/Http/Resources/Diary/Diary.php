<?php

namespace App\Http\Resources\Diary;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Api representation of Diary data.
 * @property int $id
 * @property int $weight
 * @property int $waist
 * @property int $upper_arm
 * @property int $leg
 * @property int $mood
 * @property int $created_at
 * @property int $updated_at
 * @package App\Http\Resources\Diary
 */
class Diary extends JsonResource
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
            'id'         => $this->id,
            'weight'     => $this->weight,
            'waist'      => $this->waist,
            'upper_arm'  => $this->upper_arm,
            'leg'        => $this->leg,
            'mood'       => $this->mood,
            'created_at' => parseDateString($this->created_at, 'Y-m-d H:i:s'),
            'updated_at' => parseDateString($this->updated_at, 'Y-m-d H:i:s'),
        ];
    }
}

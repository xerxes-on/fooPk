<?php

namespace App\Http\Resources\Profile;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Subscription.
 *
 * @property-read string|int $id
 * @property-read int $challenge_id
 * @property-read int $ends_at
 * @property-read int $active
 * @property-read string $created_at
 * @property-read string updated_at
 *
 * @package App\Http\Resources
 */
class Subscription extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // TODO:: @NickMost review challenge logic
        return [
            'id'           => $this->id,
            'challenge_id' => $this->challenge_id,
            'ends_at'      => $this->ends_at,
            'active'       => $this->active,
            'created_at'   => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'   => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}

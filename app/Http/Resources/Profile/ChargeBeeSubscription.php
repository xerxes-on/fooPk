<?php

namespace App\Http\Resources\Profile;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ChargeBeeSubscription.
 *
 * @property-read string|int $id
 * @property-read object $data
 *
 * @package App\Http\Resources
 */
class ChargeBeeSubscription extends JsonResource
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
            'id'   => $this?->id,
            'data' => $this?->data,
        ];
    }
}

<?php

namespace App\Http\Resources\AOK;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title'     => $this->title,
            'min_price' => $this->min_price,
            'max_price' => $this->max_price,
        ];
    }
}

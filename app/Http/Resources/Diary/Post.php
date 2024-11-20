<?php

namespace App\Http\Resources\Diary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Api representation of Diary data.
 *
 * @property \App\Models\Post $resource
 *
 * @package App\Http\Resources\Diary
 */
final class Post extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->resource->id,
            'content'    => $this->resource->content,
            'image'      => asset($this->resource->image->url('medium')),
            'created_at' => $this->resource->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->resource->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}

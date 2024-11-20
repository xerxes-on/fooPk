<?php

declare(strict_types=1);

namespace Modules\Course\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;

/**
 * Course Resource.
 *
 * @property-read \Modules\Course\Models\Course $resource
 * @package Modules\Course\Http\Resources
 */
final class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageStyle = File::exists($this->resource->image->path('mobile')) ? 'mobile' : 'thumb';
        $return     = [
            'id'          => $this->resource->id,
            'title'       => $this->resource->title,
            'duration'    => $this->resource->duration,
            'description' => $this->resource->description,
            'foodpoints'  => $this->resource->foodpoints,
            'image'       => asset($this->resource->image->url($imageStyle)),
        ];
        if (isset($this->resource->pivot)) {
            $return['start_at'] = $this->resource->pivot->start_at;
            $return['ends_at']  = $this->resource->pivot->ends_at;
        }

        $return['labels'] = [
            'start_at'      => trans('course::common.date_start'),
            'ends_at'       => trans('course::common.date_end'),
            'total_days'    => trans('course::common.total'),
            'view_more'     => trans('api.view_more'),
            'buy_challenge' => trans('course::common.confirm_purchase'),
            'cancel'        => trans('common.cancel'),
        ];
        return $return;
    }
}

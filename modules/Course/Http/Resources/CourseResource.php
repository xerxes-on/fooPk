<?php

declare(strict_types=1);

namespace Modules\Course\Http\Resources;

use Carbon\Carbon;
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
        $today      = Carbon::now()->startOfDay();
        $return     = [
            'id'               => $this->resource->id,
            'title'            => $this->resource->title,
            'duration'         => $this->resource->duration,
            'description'      => $this->resource->description,
            'foodpoints'       => $this->resource->foodpoints,
            'image'            => asset($this->resource->image->url($imageStyle)),
            'minimum_start_at' => $this->resource->minimum_start_at instanceof Carbon ? $this->resource->minimum_start_at : $today,
        ];
        if (isset($this->resource->pivot)) {
            $return['start_at'] = $this->resource->pivot->start_at;
            $return['ends_at']  = $this->resource->pivot->ends_at;

        }
        // Ensure minimum start date is at least today
        $return['minimum_start_at'] = $return['minimum_start_at']->startOfDay();
        if ($return['minimum_start_at']->lt($today)) {
            $return['minimum_start_at'] = $today;
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

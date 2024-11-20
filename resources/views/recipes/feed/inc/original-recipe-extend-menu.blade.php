@php
    $urlRoute = route(
				'recipe.show',
				[
					'id'        => $item->id,
					'date'      => $date->format('Y-m-d'),
					'ingestion' => $item->pivot->meal_time
				]
			);
@endphp
<a href="{{$urlRoute}}"
   class="week-grid_item_link"
   aria-label="{{ $item->title }}">
    <img src="{{ $item->image->url('thumb') }}" alt="{{ $item->title }}" class="week-grid_item_img">
</a>

<div class="week-grid_item_extend">
    <div class="week-grid_item_extend_content">
        <a href="{{$urlRoute}}" class="week-grid_item_extend_content_title">{{ $item->title }}</a>

        <div class="week-grid_item_extend_content_complexity">
            <vue-stars
                    name="{{ strtotime($date) . $key }}_complexity"
                    :max="3"
                    @if(!is_null($item?->complexity))
                        :value="{{ $item->complexity->id }}"
                    @endif
                    :readonly="true">
                <img slot-scope="props"
                     slot="activeLabel"
                     src="{{asset('/images/icons/ic_hat_black.svg')}}"
                     width="24"
                     height="24"
                     alt="Icon"
                />
                <img slot-scope="props"
                     slot="inactiveLabel"
                     src="{{asset('/images/icons/ic_hat_black_empty.svg')}}"
                     width="24"
                     height="24"
                     alt="Icon"
                />
            </vue-stars>
        </div>

        <div class="week-grid_item_extend_content_info">
            <div class="week-grid_item_extend_content_info_time">
				<span class="week-grid_item_extend_content_info_time_text">
					@if(isset($item->cooking_time) && isset($item->unit_of_time))
                        {{ $item->cooking_time . ' ' . trans('common.'.$item->unit_of_time) }}
                    @else
                        -
                    @endif
				</span>
            </div>
            <div class="week-grid_item_extend_content_info_favourites">
                <favorite :recipe="{{ $item->id }}"
                          :favorited="{{ is_null($item->favorite) ? 'false' : 'true' }}">
                </favorite>
            </div>
        </div>
    </div>
</div>

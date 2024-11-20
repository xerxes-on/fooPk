@php
    $route = route('recipes.flexmeal.show_one', [
        'id'        => $item->id,
        'date'      => \Carbon\Carbon::parse($item->pivot->meal_date)->format('Y-m-d'),
        'ingestion' => $ingestion->key
        // Due to fact that user can replace dinner with lunch, we must pass ingestion iteration, not flexmeal mealtime
    ]);
@endphp
<a href="{{ $route }}" aria-label="{{ $item->name }}">
    <img class="week-grid_item_img" src="{{ $item->image->url('mobile') }}" alt="{{ $item->name }}">
</a>

<div class="week-grid_item_extend">
    <div class="week-grid_item_extend_content">
        <a href="{{ $route }}" class="week-grid_item_extend_content_title">
            {{ $item->name }}
        </a>
    </div>
</div>

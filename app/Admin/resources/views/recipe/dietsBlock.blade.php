<div class="mb-3">
    <h4>@lang('common.diets')</h4>
    <p id="recipe-diets">
        @if(count($recipe->diets ?? []) > 0)
            {{ implode('; ', $recipe->diets->pluck('name')->toArray()) }}
        @else
            @lang('common.no_diets')
        @endif
    </p>
</div>

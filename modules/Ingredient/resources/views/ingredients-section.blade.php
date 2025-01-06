<section class="recipe-detail_panel">
    <div class="recipe-detail_panel_wrap">
        <h2 class="recipe-detail_panel_title">@lang('ingredient::common.ingredients')</h2>
        <x-ingredient-piece-switch></x-ingredient-piece-switch>
    </div>
    <div class="alert alert-danger" role="alert" id="calculation_alert" style="display: none;">
        {{-- TODO: should error be explained in here? --}}
        @lang('common.calculation_fails')
    </div>
    @php
       $isForMealPlan = !is_null($ingestion);
    @endphp
    @if( !empty($calculatedIngredients) )
        @foreach($calculatedIngredients as $ingredient)
            <x-recipe-ingredient :$ingredient :$recipe :$recipeType :$isForMealPlan></x-recipe-ingredient>
        @endforeach
    @endif
</section>

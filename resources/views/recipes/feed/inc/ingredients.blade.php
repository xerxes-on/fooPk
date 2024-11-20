@foreach($calculatedIngredients as $ingredient)
    @php $isNotSpice = $ingredient['main_category'] != \Modules\Ingredient\Enums\IngredientCategoryEnum::SEASON->value; @endphp

    <div class="{{ $ingredient['ingredient_type'] }}-ingredient recipe-detail_panel_ingredient-title"
         data-id="{{ $ingredient['ingredient_id'] }}"
         data-main_category="{{ $ingredient['main_category'] }}"
         data-amount="{{ $ingredient['ingredient_amount'] }}"
         data-type="{{ $ingredient['ingredient_type'] }}">

        <div class="ingredient-text">
            @if($ingredient['ingredient_amount'] > 0)
                <span class="ingredient-amount-anchor"
                      data-amount="{{ $ingredient['ingredient_amount'] }}"
                      data-unit="{{$ingredient['ingredient_unit']}}">
					{{ $ingredient['ingredient_amount'] }} {{ $ingredient['ingredient_unit'] }}
				</span>
            @endif
            {{ $ingredient['ingredient_name'] }}
            <x-ingredient-tip :data="$ingredient['hint'] ?? []"></x-ingredient-tip>
        </div>
        <div class="ingredient-replace-block print-hide"></div>

        @if(!is_null($ingestion) && $ingredient['allow_replacement'] == true)
            <div class="control-block print-hide">
                <button type="button"
                        class="btn-with-icon btn-with-icon-edit replace-ingredient"
                        aria-label="@lang('common.replace_ingredient')"
                        title="@lang('common.replace_ingredient')"></button>
                <button type="button"
                        class="btn-with-icon btn-with-icon-close cancel-replacement"
                        aria-label="@lang('common.cancel')"
                        title="@lang('common.cancel')"
                        style="display:none;">
                </button>
                <button type="button"
                        class="btn-with-icon btn-with-icon-check confirm-replacement"
                        aria-label="@lang('common.confirm')"
                        title="@lang('common.confirm')"
                        style="display:none;">
                </button>
            </div>

        @elseif($isNotSpice)
            <apply-recipe
                    :recipe="{{ $recipe->id }}"
                    :meal-time='{!! json_encode($recipe->ingestions->pluck('key', 'id')->toArray()) !!}'
                    :recipe-type="{{ $recipeType }}"
                    :is-ingestion="false"></apply-recipe>
        @endif
    </div>
@endforeach

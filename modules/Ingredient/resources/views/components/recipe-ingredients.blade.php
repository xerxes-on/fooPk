<div class="{{ $ingredient['ingredient_type'] }}-ingredient recipe-detail_panel_ingredient-title"
     data-id="{{ $ingredient['ingredient_id'] }}"
     data-main_category="{{ $ingredient['main_category'] }}"
     data-amount="{{ $ingredient['ingredient_amount'] }}"
     data-type="{{ $ingredient['ingredient_type'] }}">

    <div class="ingredient-text js-convert-ingredients" data-is-convertable="{{ (int)$isConvertable }}"
         data-portion-modifier="1">
        @if($ingredient['ingredient_amount'] > 0)
            <span class="ingredient-amount-anchor"
                  data-amount="{{ $ingredient['ingredient_amount'] }}"
                  data-unit="{{$ingredient['ingredient_unit']}}"
                  @if($isConvertable)
                      data-piece="{{$ingredient['conversion_data']['ingredient_conversion_amount']}}"
                  data-fraction-map="{{json_encode($ingredient['conversion_data']['fraction_map'], JSON_THROW_ON_ERROR)}}"
                    @endif>
					{{ $ingredient['ingredient_amount'] }} {{ $ingredient['ingredient_unit'] }}
				</span>
        @endif
        <span class="ingredient-title-anchor"
              data-text="{{ $ingredient['ingredient_name'] }}"
              @if($isConvertable)
                  data-text-multiple="{{$ingredient['conversion_data']['ingredient_plural_name']}}"
              data-alternative-unit="{{$ingredient['conversion_data']['ingredient_alternative_unit']}}"
                @endif>
            {{ $ingredient['ingredient_name'] }}
        </span>
        <x-ingredient-tip :data="$ingredient['hint'] ?? []"></x-ingredient-tip>
    </div>
    <div class="ingredient-replace-block print-hide"></div>

    @if($isForMealPlan && $ingredient['allow_replacement'] === true)
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
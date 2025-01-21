<div class="shopping-list_ingredients" id="ingredient_container">
    @php($isPrintPage = Route::currentRouteName() === 'purchases.list.print')
    @forelse($ingredient_categories as $category)
        <div class="card"
             id="{{ is_null($category['category']['id']) ? 'custom' : 'category_'.$category['category']['id'] }}">
            <div class="shopping-list-title-wrapper">
                <h4 class="shopping-list_ingredients_title">
                    <b>
                        @if(is_null($category['category']['id']))
                            {{ __('shopping-list::common.labels.custom') }}
                        @else
                            {{ ucfirst($category['category']['name']) }}
                        @endif
                    </b>
                </h4>
                @if($loop->first && !$isPrintPage)
                    <x-ingredient-piece-switch></x-ingredient-piece-switch>
                @endif
            </div>
            <ul class="shopping-list_ingredients_list">
                @foreach($category['ingredients'] as $ingredient)
                    @php($isConvertable = (isset($ingredient['conversion_data']) &&  $ingredient['conversion_data']!== []))
                    <li class="shopping-list_ingredients_list_item" id="ingredient_{{ $ingredient['id'] }}"
                        data-is-convertable="{{(int)$isConvertable}}">

                        <input type="checkbox"
                               class="form-check ingredient-list-element shopping-list_ingredients_list_item_check"
                               {{ $ingredient['completed'] ? 'checked' : '' }}
                               data-item-id="{{ $ingredient['id'] }}" id="{{ $ingredient['id'] }}">

                        <label for="{{ $ingredient['id'] }}" class="shopping-list_ingredients_list_item_label">

                            <span class="cross-line" aria-hidden="true"></span>
                            @if(isset($ingredient['custom_title']))
                                {{ $ingredient['custom_title'] }}
                            @else
                                @if($category['category']['id'] != 80 && $ingredient['amount'] > 0)
                                    <span class="shopping-list_ingredients_list_item_amount"
                                          @if($isConvertable)
                                              data-unit="{{$ingredient['unit']}}"
                                          data-amount="{{$ingredient['amount']}}"
                                          data-piece="{{$ingredient['conversion_data']['ingredient_conversion_amount']}}"
                                          data-alternative-unit="{{$ingredient['conversion_data']['ingredient_alternative_unit']}}"
                                          data-text-multiple="{{$ingredient['conversion_data']['ingredient_plural_name']}}"
                                          data-alternative-unit="{{$ingredient['conversion_data']['ingredient_alternative_unit']}}"
                                          data-fraction-map="{{json_encode($ingredient['conversion_data']['fraction_map'], JSON_THROW_ON_ERROR)}}"
                                          @endif
                                    >
                                        {{ $ingredient['amount'] }} {{ $ingredient['unit'] }}
                                    </span>
                                @endif
                                <span class="shopping-list_ingredients_list_item_text"
                                      @if($isConvertable)
                                          data-text="{{$ingredient['name']}}"
                                      data-text-multiple="{{$ingredient['conversion_data']['ingredient_plural_name']}}"
                                      @endif
                                >{{ $ingredient['name'] }}</span>
                            @endif

                        </label>
                        @if(!$isPrintPage)
                            <x-ingredient-tip :data="$ingredient['hint'] ?? []"></x-ingredient-tip>
                            <button type="button"
                                    class="shopping-list_recipes_item_right_label_rounded ingredient-delete-anchor btn-with-icon btn-with-icon-delete"
                                    data-id="{{ $ingredient['id'] }}"
                                    data-category-id="{{ 'category_'.$category['category']['id'] }}"
                                    onclick="window.foodPunk.deleteIngredient(this)"
                                    title="@lang('common.delete_ingredient')"
                                    aria-label="@lang('common.delete_ingredient')"></button>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @empty
        <span id="empty_ingredients_list">@lang('shopping-list::messages.success.empty_ingredients_list')</span>
    @endforelse
</div>

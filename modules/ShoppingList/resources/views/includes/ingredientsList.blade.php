<div class="shopping-list_ingredients" id="ingredient_container">
    @php($isPrintPage = Route::currentRouteName() === 'purchases.list.print')
    @forelse($ingredient_categories as $category)
        <div class="card"
             id="{{ is_null($category['category']['id']) ? 'custom' : 'category_'.$category['category']['id'] }}">
            <h4 class="shopping-list_ingredients_title"><b>{{ ucfirst($category['category']['name']) }}</b></h4>
            <ul class="shopping-list_ingredients_list">
                @foreach($category['ingredients'] as $ingredient)
                    <li class="shopping-list_ingredients_list_item" id="ingredient_{{ $ingredient['id'] }}">

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
                                    <span class="shopping-list_ingredients_list_item_amount">
                                            {{ $ingredient['amount'] }} {{ $ingredient['unit'] }}
                                    </span>
                                @endif
                                <span>{{ $ingredient['name'] }}</span>
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

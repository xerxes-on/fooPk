<div class="mb-3">
    <h4>@lang('common.variable_ingredients')</h4>
    <table class="table-primary table table-striped">
        <thead>
        <tr>
            <th class="row-header">@lang('common.type')</th>
            <th class="row-header">@lang('common.name')</th>
            <th class="row-header">@lang('common.diets')</th>
            <th class="row-header">@lang('common.proteins')</th>
            <th class="row-header">@lang('common.fats')</th>
            <th class="row-header">@lang('common.carbohydrates')</th>
            <th class="row-header">@lang('common.calories')</th>
            <th class="row-header">@lang('common.amount')</th>
        </tr>
        </thead>
        <tbody id="variable_ingredient_tbody">
        @php
            $ingredientCategoryIDs = $ingredients_categories->pluck('id')->toArray();
            $rows = is_null($recipe) ?
                     null :
                     $recipe->variableIngredients()
                           ->wherePivotIn('ingredient_category_id', $ingredientCategoryIDs)
                           ->get();
        @endphp

        @foreach($ingredients_categories as $ingredient_category)
            @php
                $row = is_null($rows) ?
                        null :
                         $rows->first(fn($item) => $item->pivot->ingredient_category_id == $ingredient_category->id);
                $countedRows = count($row?->category?->diets ?? []);
            @endphp

            <tr id="ingredient_category_{{ $ingredient_category->id }}_row">
                <td>{{ $ingredient_category->name }}</td>
                <td class="row-text">
                    <select class="autocomplete-select variable-ingredient-anchor choosen-ingredient-anchor"
                            name="variable_ingredients[{{ $ingredient_category->id }}][ingredient_id]"
                            data-category-id="{{ $ingredient_category->id }}"
                            data-ajax--url="{{route('admin.search-ingredients.select2')}}"
                            data-ajax--cache="true"
                            data-ajax--delay="400"
                            data-placeholder="Search for ingredients"
                            data-width="150"
                            data-minimum-input-length="1">
                        <option value="0"
                                data-proteins="—"
                                data-fats="—"
                                data-carbohydrates="—"
                                data-calories="—"
                                data-unit="—"
                                data-unit-default-amount=""
                                data-diets=""
                                {{ is_null($row) ? 'selected' : '' }}>
                        </option>
                        @if(!is_null($row))
                            <option value="{{ $row->id }}"
                                    data-proteins="{{ $row->proteins }}"
                                    data-fats="{{ $row->fats }}"
                                    data-carbohydrates="{{ $row->carbohydrates }}"
                                    data-calories="{{ $row->calories }}"
                                    data-unit="{{ $row->unit->full_name }}"
                                    data-unit-default-amount="{{ $row->unit->default_amount }}"
                                    data-diets="{{ $countedRows ? implode('|', $row->category->diets->pluck('name')->toArray()) : '' }}"
                                    selected="selected">
                                {{ $row->name }}
                            </option>
                        @endif
                    </select>
                    <input type="hidden"
                           name="variable_ingredients[{{ $ingredient_category->id }}][category_id]"
                           value="{{ $ingredient_category->id }}"/>
                </td>
                <td class="row-text diets">
                    <div class="d-flex">
                        @if(!is_null($row) && $countedRows)
                            @foreach($row->category->diets as $diet)
                                <span class="label label-info mr-1">{{$diet->name}}</span>
                            @endforeach
                        @else
                            —
                        @endif
                    </div>
                </td>
                <td class="row-text proteins">
                    {{ is_null($row) ? '-' :$row->proteins}}
                </td>
                <td class="row-text fats">
                    {{ is_null($row) ? '-' :$row->fats}}
                </td>
                <td class="row-text carbohydrates">
                    {{ is_null($row) ? '-' :$row->carbohydrates}}
                </td>
                <td class="row-text calories">
                    {{ is_null($row) ? '-' :$row->calories}}
                </td>
                <td class="row-text units">
                    {{ is_null($row) ? '-' :$row->unit->full_name}}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

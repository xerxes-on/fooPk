<div class="mb-3">
    <h4>@lang('ingredient::common.ingredients')</h4>
    <table class="table-primary table table-striped">
        <thead>
        <tr>
            <th class="row-header">@lang('common.amount')</th>
            <th class="row-header">@lang('common.units')</th>
            <th class="row-header">@lang('common.name')</th>
            <th class="row-header">@lang('common.diets')</th>
            <th class="row-header">@lang('common.proteins')</th>
            <th class="row-header">@lang('common.fats')</th>
            <th class="row-header">@lang('common.carbohydrates')</th>
            <th class="row-header">@lang('common.calories')</th>
            <th class="row-header"></th>
        </tr>
        </thead>
        <tbody id="ingredient_tbody">
        @if(!empty($recipe) && !empty($recipe->ingredients))
            @foreach($recipe->ingredients as $key => $recipeIngredient)
                <tr class="ingredient" id="ingredient_{{ $key }}_row">
                    <td class="row-text">
                        <input class="form-control amount-anchor"
                               type="number"
                               value="{{ $recipeIngredient->pivot->amount }}"
                               name="ingredients[{{ $key }}][amount]" data-row-id="{{ $key }}"/>
                    </td>
                    <td class="row-text units" id="ingredient_{{ $key }}_units">
                        {{ $recipeIngredient->unit->full_name }}
                    </td>

                    <td class="row-text">
                        @php
                            // This is a workaround is introduced to fix massive duplicated queries while obtaining diets names
                                $user  = $user ?? auth()->user();
                                $diets = [];
                                if ($recipeIngredient?->category?->diets?->count()) {
                                    $recipeIngredient?->category?->diets->each(function ($diet) use (&$diets) {
                                        $diets[] = $diet->translations->pluck('name', 'locale')->toArray();
                                    });
                                }
                                $sortedDiets = '';
                                if (!empty($diets)) {
                                    foreach ($diets as $diet) {
                                        $sortedDiets .= isset($diet[$user->lang]) ? $diet[$user->lang] : reset($diet);
                                        $sortedDiets .= '|';
                                    }
                                    $sortedDiets = trim($sortedDiets, '|');
                                }
                        @endphp
                        <select class="ingredient-id ingredient-picker-anchor autocomplete-select choosen-ingredient-anchor"
                                id="ingredient_{{ $key }}_select"
                                name="ingredients[{{ $key }}][ingredient_id]"
                                data-row-id="{{ $key }}"
                                data-ajax--url="{{route('admin.search-ingredients.select2')}}"
                                data-ajax--delay="400"
                                data-placeholder="Search for ingredients"
                                data-width="150"
                                data-minimum-input-length="1">
                            <option value="0"
                                    data-proteins="0"
                                    data-fats="0"
                                    data-carbohydrates="0"
                                    data-calories="0"
                                    data-diets=""
                                    data-unit="none">
                            </option>

                            <option value="{{ $recipeIngredient->id }}"
                                    data-proteins="{{ $recipeIngredient->proteins }}"
                                    data-fats="{{ $recipeIngredient->fats }}"
                                    data-carbohydrates="{{ $recipeIngredient->carbohydrates }}"
                                    data-calories="{{ $recipeIngredient->calories }}"
                                    data-unit="{{ $recipeIngredient->unit->full_name }}"
                                    data-unit-default-amount="{{ $recipeIngredient->unit->default_amount }}"
                                    data-diets="{{ $sortedDiets }}"
                                    selected="selected">
                                {{ $recipeIngredient->name }}
                            </option>
                        </select>
                    </td>
                    <td class="row-text diets" id="ingredient_{{ $key }}_diets">
                        <div class="d-flex">
                            @forelse(explode('|',$sortedDiets) as $diet)
                                <span class="label label-info mr-1">{{ $diet }}</span>
                            @empty
                                —
                            @endforelse
                        </div>
                    </td>
                    <td class="row-text proteins" id="ingredient_{{ $key }}_proteins">
                        {{ round($recipeIngredient->proteins / 100 * $recipeIngredient->pivot->amount, 2) }}
                    </td>
                    <td class="row-text fats" id="ingredient_{{ $key }}_fats">
                        {{ round($recipeIngredient->fats / 100 * $recipeIngredient->pivot->amount, 2) }}
                    </td>
                    <td class="row-text carbohydrates" id="ingredient_{{ $key }}_carbohydrates">
                        {{ round($recipeIngredient->carbohydrates / 100 * $recipeIngredient->pivot->amount, 2) }}
                    </td>
                    <td class="row-text calories" id="ingredient_{{$key}}_calories">
                        {{ round($recipeIngredient->calories / 100 * $recipeIngredient->pivot->amount, 2) }}
                    </td>
                    <td class="row-text">
                        <button class="delete-ingredient-anchor button-round btn-danger"
                                type="button"
                                data-id="{{ $key }}"
                                aria-label="Delete ingredient">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        @endif
        </tbody>
        <tfoot>
        <tr>
            <td class="row-text">@lang('common.total')</td>
            <td class="row-text"></td>
            <td class="row-text"></td>
            <td class="row-text"></td>
            <td class="row-text" id="total_proteins"><b>{{ $recipe?->proteins ?? 0 }}</b></td>
            <td class="row-text" id="total_fats"><b>{{ $recipe?->fats ?? 0 }}</b></td>
            <td class="row-text" id="total_carbohydrates"><b>{{ $recipe?->carbohydrates ?? 0 }}</b></td>
            <td class="row-text" id="total_calories"><b>{{ $recipe?->calories ?? 0 }}</b></td>
            <td class="row-text"></td>
        </tr>

        {{-- js pattern pattern--}}
        <tr id="row_pattern" style="display: none">
            <td class="row-text">
                <input class="form-control amount-anchor" type="number" value="0"/>
            </td>
            <td class="row-text units">none</td>
            <td class="row-text">
                <select class="ingredient-id ingredient-picker-anchor"
                        data-ajax--url="{{route('admin.search-ingredients.select2')}}"
                        data-ajax--delay="400"
                        data-placeholder="Search for ingredients"
                        data-minimum-input-length="1"
                        data-width="150">
                    <option value="0"
                            data-proteins="0"
                            data-fats="0"
                            data-carbohydrates="0"
                            data-calories="0"
                            data-unit="gramms"
                            selected>
                    </option>
                </select>
            </td>
            <td class="row-text diets">—</td>
            <td class="row-text proteins">0</td>
            <td class="row-text fats">0</td>
            <td class="row-text carbohydrates">0</td>
            <td class="row-text calories">0</td>
        </tr>
        </tfoot>
    </table>

    <p><small class="form-element-helptext">@lang('admin_help_text.ingredients_help_text')</small></p>

    <input type="hidden" name="total[proteins]" id="total_proteins_input" value="{{ $recipe?->proteins ?? 0 }}"/>
    <input type="hidden" name="total[fats]" id="total_fats_input" value="{{ $recipe?->fats ?? 0 }}"/>
    <input type="hidden" name="total[carbohydrates]" id="total_carbohydrates_input"
           value="{{ $recipe?->carbohydrates ?? 0 }}"/>
    <input type="hidden" name="total[calories]" id="total_calories_input" value="{{ $recipe->calories ?? 0 }}"/>

    @if($ingredients > 0)
        <button id="new_ingredient_btn" class="btn btn-primary" type="button">@lang('common.add_ingredient')</button>
    @else
        <p>@lang('common.no_ingredients')</p>
    @endif
</div>

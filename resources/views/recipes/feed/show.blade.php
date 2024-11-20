@extends('layouts.app')

@section('styles')
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet">
@endsection

@section('title', trans('common.recipes'))

@section('content')
    <div class="container recipe-detail">
        <div class="row">
            <div class="col-xs-12">
                @isset($custom_common)
                    <h1>{{ $recipe->title.' *'.trans('common.edited').'*' }}</h1>
                @else
                    <h1>{{ $recipe->title }}</h1>
                @endif

                @if(!empty($recipe->calc_invalid))
                    <div class="invalid_recipe alert alert-danger">@lang('common.recipe_is_invalid')</div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-5 pull-right">
                <div class="row visible-xs">
                    <div class="col-xs-12">
                        <img src="{{ asset($recipe->image->url('large')) }}"
                             alt="{{ $recipe->title }}"
                             width="100%"
                             class="recipe-detail_img img-responsive">
                    </div>
                </div>

                <!-- Details panel -->
                <div class="recipe-detail_panel">
                    <div class="recipe-detail_panel_right">

                        <!-- add to Favorites -->
                        <div class="recipe-detail_panel_right_circle">
                            <favorite
                                    :recipe="{{ $recipe->id }}"
                                    :favorited="{{ $recipe->favorited() ? 'true' : 'false' }}"
                            ></favorite>
                        </div>
                        @isset($ingestion)
                            <!-- add to Shopping cart -->
                            <div class="recipe-detail_panel_right_circle">
                                <purchases-list
                                        :recipe-id="{{ $custom_common ?? $recipe->id }}"
                                        :recipe-type="{{ $recipeType }}" {{-- Must be string --}}
                                        :meal-date="{{ json_encode(parseDateString($date)) }}"
                                        :mealtime="{{ $ingestion->id }}" {{-- Must be string --}}
                                ></purchases-list>
                            </div>
                        @else
                            <!-- add to meal plan -->
                            <div class="recipe-detail_panel_right_circle">
                                <apply-recipe
                                        :recipe="{{ $recipe->id }}"
                                        :recipe-type="{{ $recipeType }}"
                                        :meal-time="{{ json_encode($recipe->ingestions->pluck('key', 'id')->toArray()) }}"
                                ></apply-recipe>
                            </div>
                        @endif

                        <!-- Print action -->
                        <div class="recipe-detail_panel_right_circle">
                            <a href="{{ request()->url() .'?print' }}">
                                <img alt="" role="presentation" src='{{ asset("/images/icons/ic_printer.svg") }}'
                                     width="24"/>
                            </a>
                        </div>

                        @isset($custom_common)
                            <div class="recipe-detail_panel_right_circle">
                                <a href="{{ route('recipes.own.restore', ['id' => $custom_common]) }}">
                                    <img alt=""
                                         role="presentation"
                                         src='{{ asset("/images/icons/baseline_replay.svg") }}'
                                         width="24"/>
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="recipe-detail_panel_title">{{ trans('common.details') }}</div>

                    <!-- BREAKFAST / LUNCH / Dinner -->
                    <div class="recipe-detail_panel_line">
                        <div class="recipe-detail_panel_line_label">
                            {{ implode(' / ', $recipe->ingestions->pluck('title')->toArray()) }}
                        </div>
                    </div>

                    <!-- Cost-->
                    <div class="recipe-detail_panel_line">
                        <div class="recipe-detail_panel_line_label">
                            <vue-stars
                                    name="price"
                                    :max="3"
                                    @if(!is_null($recipe->price))
                                        :value="{{ $recipe->price->id }}"
                                    @endif
                                    :readonly="true">
                                <img alt="" slot-scope="props" slot="activeLabel" src="/images/icons/ic_money.svg"
                                     width="24"
                                     height="24"/>
                                <img alt="" slot-scope="props" slot="inactiveLabel"
                                     src="/images/icons/ic_money_noactive.svg"
                                     width="24"
                                     height="24"/>
                            </vue-stars>
                        </div>
                    </div>

                    <!-- Complexity-->
                    <div class="recipe-detail_panel_line">
                        <div class="recipe-detail_panel_line_label">
                            <vue-stars
                                    name="complexity"
                                    :max="3"
                                    @if(!is_null($recipe->complexity))
                                        :value="{{ $recipe->complexity->id }}"
                                    @endif
                                    :readonly="true">
                                <img alt="" slot-scope="props" slot="activeLabel" src="/images/icons/ic_hat_black.svg"
                                     width="24"
                                     height="24"/>
                                <img alt="" slot-scope="props" slot="inactiveLabel"
                                     src="/images/icons/ic_hat_black_empty.svg"
                                     width="24"
                                     height="24"/>
                            </vue-stars>
                        </div>
                    </div>

                    <!-- cooking time -->
                    <div class="recipe-detail_panel_line">
                        <div class="recipe-detail_panel_line_label">
                            <div class="pull-left">
                                <img alt="" role="presentation" src="{{ asset("/images/icons/ic_timer.svg") }}"
                                     width="24"/>
                            </div>
                            @if(isset($recipe->cooking_time) && isset($recipe->unit_of_time))
                                {{ $recipe->cooking_time . ' ' . trans('common.'.$recipe->unit_of_time) }}
                            @else
                                <img alt="" role="presentation" src="{{ asset("/images/icons/ic_subtract.svg") }}"/>
                            @endif
                        </div>
                    </div>

                    @if($recipe->diets->count() > 0)
                        <div class="recipe-detail_panel_line">
                            <div class="recipe-detail_panel_line_label">
                                {{ implode(' / ', $recipe->diets->pluck('name')->toArray()) }}
                            </div>
                        </div>
                    @endif

                    @if(isset($additionalInfo) &&
                         (
                            key_exists('calculated_KH', $additionalInfo)||
                            key_exists('calculated_EW', $additionalInfo)||
                            key_exists('calculated_F', $additionalInfo)||
                            key_exists('calculated_KCal', $additionalInfo)
                        )
                    )
                        <table class="line-carbohydrates">
                            @if(key_exists('calculated_KH', $additionalInfo))
                                <tr>
                                    <th>@lang('common.carb') (g)</th>
                                    <th>{{ $additionalInfo['calculated_KH'] }}</th>
                                </tr>
                            @endif

                            @if(key_exists('calculated_EW', $additionalInfo))
                                <tr>
                                    <td>@lang('common.protein') (g)</td>
                                    <td>{{ $additionalInfo['calculated_EW'] }}</td>
                                </tr>
                            @endif

                            @if(key_exists('calculated_F', $additionalInfo))
                                <tr>
                                    <td>@lang('common.fat') (g)</td>
                                    <td>{{ $additionalInfo['calculated_F'] }}</td>
                                </tr>
                            @endif

                            @if(key_exists('calculated_KCal', $additionalInfo))
                                <tr>
                                    <td>@lang('common.calories_word')</td>
                                    <td>{{ $additionalInfo['calculated_KCal'] }}</td>
                                </tr>
                            @endif
                        </table>
                    @endif

                </div>

                <!-- Tools -->
                @if(count($recipe->inventories))
                    <div class="recipe-detail_panel">
                        <div class="recipe-detail_panel_title">@lang('common.tools')</div>
                        @foreach($recipe->inventories as $inventory)
                            <div class="recipe-detail_panel_tool-title">
                                {{ $inventory->title }}
                            </div>
                        @endforeach
                    </div>
                @endif
                @if(!is_null($ingestion))
                    <div class="recipe-detail_panel">
                        <label for="portions" class="recipe-detail_panel_title">@lang('common.portions')</label>
                        <select id="portions" class="recipe-detail_panel_select">
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>

                        <cooking-mode :i18n="{{json_encode(trans('common.cooking_mode'))}}"></cooking-mode>
                    </div>
                @endif

                <!-- Ingredients -->
                <div class="recipe-detail_panel">
                    <div class="recipe-detail_panel_title">@lang('common.ingredients')</div>

                    <div class="alert alert-danger" role="alert" id="calculation_alert" style="display: none;">
                        {{-- TODO: should error be explained in here? --}}
                        @lang('common.calculation_fails')
                    </div>

                    @if( !empty($calculatedIngredients) )
                        @include('recipes.feed.inc.ingredients')
                    @endif
                </div>
            </div>

            <div class="col-sm-7" style="display: inline-block;">
                <div class="row hidden-xs">
                    <div class="col-xs-12">
                        <img src="{{ asset($recipe->image->url('large')) }}"
                             alt="{{ $recipe->title }}"
                             width="100%"
                             class="recipe-detail_img img-responsive">
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        @php($steps = $recipe->steps()->get())
                        @if($steps->isNotEmpty())
                            <div class="recipe-detail_steps">
                                <h2>@lang('common.steps_to_prepare')</h2>

                                <ol class="recipe-detail_steps_list" start="0">
                                    @foreach($steps as $step)
                                        <li>{{ $step['description'] }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/i18n/{{auth()->user()->lang ?? 'de'}}.min.js"
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>

        $('#portions').on('change', function () {
            const portions = parseInt($(this).val());

            $('.ingredient-amount-anchor').each(function () {
                $(this).text(parseFloat($(this).attr('data-amount')) * portions + ' ' + $(this).attr('data-unit'));
            });
        });

        @if(!is_null($ingestion))
        $('.replace-ingredient').on('click', function () {
            $('.confirm-replacement').hide();
            $('.cancel-replacement').hide();
            $('.ingredient-replace-block').hide();
            $('.ingredient-text').show();
            $('.replace-ingredient').show();
            $('#calculation_alert').hide();

            showIngredientsActions($(this).parent().parent());
        });

        $('.cancel-replacement').on('click', function () {

            const li = $(this).parent().parent();

            $(this).hide();
            li.find('.confirm-replacement').hide();
            li.find('.ingredient-replace-block').hide();
            li.find('.ingredient-text').show();
//            li.find('.replace-ingredient').show();
            $('.replace-ingredient').show();
            $('#calculation_alert').hide();
            li.find('.ingredient-replace-block').html('');
        });

        $('.confirm-replacement').on('click', function () {
            if (!confirm("{{ trans('common.replace_ingredient_question') }}")) {
                return;
            }
            const li = $(this).parent().parent();

            let select = li.find('.ingredient-to-change').find(':selected'),
                fixed_ingredients = [],
                variable_ingredients = [];

            $('.fixed-ingredient').each(function () {
                let ingredientId = $(this).attr('data-id'),
                    amount = $(this).attr('data-amount'),
                    ingredient_category_id = $(this).attr('data-main_category'),
                    fixed_ingredient = {
                        ingredient_id: parseInt(ingredientId),
                        ingredient_category_id: parseInt(ingredient_category_id),
                        amount: parseInt(amount),
                    };

                {{--
                    To mark ingredient as replaced it should be FIXED,
                    Have the correct ID, amount (crutial as ID can be duplicated),
                    and have the same category
                 --}}
                if (li.attr('data-type') === 'fixed' &&
                    li.attr('data-id') === ingredientId &&
                    li.attr('data-main_category') === ingredient_category_id &&
                    li.attr('data-amount') === amount
                ) {
                    fixed_ingredient.replace_by = parseInt(select.val());
                }

                fixed_ingredients.push(fixed_ingredient);
            });

            $('.variable-ingredient').each(function () {
                if (li.attr('data-type') === 'variable' && li.attr('data-id') === $(this).attr('data-id')) {
                    variable_ingredients.push({
                        ingredient_id: parseInt(select.val()),
                        ingredient_category_id: parseInt($(this).attr('data-main_category')),
                    });
                } else {
                    variable_ingredients.push({
                        ingredient_id: parseInt($(this).attr('data-id')),
                        ingredient_category_id: parseInt($(this).attr('data-main_category')),
                    });
                }
            });

            {{-- /user/recipes/{id}/{meal_date}/{meal_time}--}}
            $.ajax({
                type: 'POST',
                url: "{{ route('recipes.own.create-from-recipe') }}",
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    recipe_id: {{ $recipe->id }},
                    recipe_type: {{ $recipeType }},
                    custom_recipe_id: {{ $custom_common ?? 0 }},
                    date: "{{ $date }}",
                    ingestion: {{ $ingestion->id }},
                    fixed_ingredients: fixed_ingredients,
                    variable_ingredients: variable_ingredients,
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (resp) {
                    if (resp.success) {
                        window.location = resp.route;
                    } else {
                        $('#calculation_alert').show();
                        $('#loading').hide();
                    }
                },
                error: function (err) {
                    $('#calculation_alert').show();
                    $('#loading').hide();
                },
            });
        });

        function showIngredientsActions(li) {
            $('.replace-ingredient').hide();
            li.find('.ingredient-text').hide();
            li.find('.confirm-replacement').show();
            li.find('.cancel-replacement').show();
            createIngredientSelect(li);
        }

        function createIngredientSelect(li) {
            li.find('.ingredient-replace-block').html('');
            li.find('.ingredient-replace-block').append(`<select class="ingredient-to-change"></select>`);
            li.find('.ingredient-to-change').select2({
                width: '100%',
                ajax: {
                    url: "{{route('ingredients.search.all')}}",
                    cache: true
                }
            });
            li.find('.ingredient-replace-block').show();

            li.find('.ingredient-to-change').select2('open');
        }

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        @endif
    </script>
@append
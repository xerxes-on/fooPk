@extends('layouts.print-layout')

@section('title', trans('common.recipes'))

@section('styles')
    <link href="{{ mix('vendor/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ mix('vendor/owlcarousel/assets/owl.theme.default.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="container recipe-detail">
        <div class="row">
            <div class="col-xs-12">
                <h1>{{ $recipe->title }}</h1>

                @if(!empty($recipe->calc_invalid))
                    <div class="invalid_recipe alert alert-danger">
                        {{ trans('common.recipe_is_invalid') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-sm-5 pull-right">
                <div class="row visible-xs">
                    <div class="col-xs-12">
                        <img src="{{ asset($recipe->image->url('medium')) }}"
                             alt="{{ $recipe->title }}"
                             width="100%"
                             class="recipe-detail_img img-responsive">
                    </div>
                </div>

                <!-- Details panel -->
                <div class="recipe-detail_panel">

                    <div class="recipe-detail_panel_title">{{ trans('common.details') }}</div>

                    <!-- BREAKFAST / LUNCH / Dinner-->
                    <div class="recipe-detail_panel_line">
                        <div class="recipe-detail_panel_line_label">
                            {{ implode(' / ', $recipe->ingestions->pluck('title')->toArray()) }}
                        </div>
                    </div>

                    <!-- Cost-->
                    <div class="recipe-detail_panel_line wkhtmltopdf-print-hide">
                        <div class="recipe-detail_panel_line_label">
                            <vue-stars
                                    name="price"
                                    :max="3"
                                    @if(!is_null($recipe->price))
                                        :value="{{ $recipe->price->id }}"
                                    @endif
                                    :readonly="true">
                                <img slot-scope="props" slot="activeLabel" alt=""
                                     src="{{ asset('/images/icons/ic_money.svg') }}"
                                     width="24" height="24"/>
                                <img slot-scope="props" slot="inactiveLabel"
                                     src="{{ asset('/images/icons/ic_money_noactive.svg') }}" alt="" width="24"
                                     height="24"/>
                            </vue-stars>
                        </div>
                    </div>

                    <!-- Complexity-->
                    <div class="recipe-detail_panel_line wkhtmltopdf-print-hide">
                        <div class="recipe-detail_panel_line_label">
                            <vue-stars name="complexity"
                                       :max="3"
                                       @if(!is_null($recipe->complexity))
                                           :value="{{ $recipe->complexity->id }}"
                                       @endif
                                       :readonly="true">
                                <img slot-scope="props" slot="activeLabel" alt=""
                                     src="{{ asset('/images/icons/ic_hat_black.svg') }}"
                                     width="24" height="24"/>
                                <img slot-scope="props" slot="inactiveLabel"
                                     src="{{ asset('/images/icons/ic_hat_black_empty.svg') }}" alt="" width="24"
                                     height="24"/>
                            </vue-stars>
                        </div>
                    </div>

                    <!-- cooking time -->
                    <div class="recipe-detail_panel_line">
                        <div class="recipe-detail_panel_line_label">
                            <div class="pull-left">
                                <img src="{{ asset("/images/icons/ic_timer.svg") }}" alt="" width="24"/>
                            </div>
                            @if(isset($recipe->cooking_time) && isset($recipe->unit_of_time))
                                {{ $recipe->cooking_time . ' ' . trans('common.'.$recipe->unit_of_time) }}
                            @else
                                <img src="{{ asset("/images/icons/ic_subtract.svg") }}" alt=""/>
                            @endif
                        </div>
                    </div>

                    @php
                        $diets = [];
                         foreach($recipe->diets as $diet) {
                             $diets[] = $diet->name;
                         }
                         $ExistsAdditionalInfo = isset($additionalInfo);
                         if ($ExistsAdditionalInfo) {
                            $existsCalculated_KH = 	key_exists('calculated_KH', $additionalInfo);
                            $existsCalculated_EW = key_exists('calculated_EW', $additionalInfo);
                            $existsCalculated_F = key_exists('calculated_F', $additionalInfo);
                            $existsCalculated_KCal = key_exists('calculated_KCal', $additionalInfo) ;
                         }
                    @endphp

                            <!-- type os recipe -->
                    <div class="recipe-detail_panel_line">
                        <div class="recipe-detail_panel_line_label">
                            {{ implode(' / ', $diets) }}
                        </div>
                    </div>
                    @if($ExistsAdditionalInfo &&
                         (
                            $existsCalculated_KH||
                            $existsCalculated_EW||
                            $existsCalculated_F||
                            $existsCalculated_KCal
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
                        <div class="recipe-detail_panel_title">{{ trans('common.tools') }}</div>
                        @foreach($recipe->inventories as $inventory)
                            <div class="recipe-detail_panel_tool-title">
                                {{ $inventory->title }}
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Ingredients -->
                <div class="recipe-detail_panel">
                    <div class="recipe-detail_panel_title">{{ trans('common.ingredients') }}</div>

                    <div class="alert alert-danger" role="alert" id="calculation_allert" style="display: none;">
                        {{ trans('common.calculation_fails') }}
                    </div>

                    @if( isset($calculatedIngredients) && !empty($calculatedIngredients) )
                        @include('recipes.feed.inc.ingredients')
                    @endif

                </div>
            </div>

            <div class="col-sm-7">
                <div class="row hidden-xs">
                    <div class="col-xs-12">
                        <img src="{{ asset($recipe->image->url('medium')) }}" alt="{{ $recipe->title }}" width="100%"
                             class="recipe-detail_img img-responsive">
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        @php($steps = $recipe->steps()->get())
                        @if($steps->isNotEmpty())
                            <div class="recipe-detail_steps">
                                <h2>{{ trans('common.steps_to_prepare') }}</h2>

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

@php
    use Modules\Ingredient\Services\IngredientConversionService;
@endphp
@extends('layouts.app')

@section('title', trans('ingredient::common.ingredients'))

@section('content')
    <div class="container recipe-detail">
        <div class="row">
            <div class="col-xs-12">
                <h1>{{ $recipe->name }}</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-sm-5 pull-right">
                <div class="row visible-xs">
                    <div class="col-xs-12">
                        <img src="{{ asset($recipe->image->url('mobile')) }}"
                             alt="{{ $recipe->name }}"
                             width="100%"
                             class="recipe-detail_img img-responsive">
                    </div>
                </div>

                <!-- Ingredients -->
                <section class="recipe-detail_panel">
                    <div class="recipe-detail_panel_right_circle">
                        <purchases-list
                                :recipe-id="{{ $recipe->id }}"
                                :recipe-type="{{ $recipeType }}"
                                :meal-date="{{ json_encode(parseDateString($recipe->meal_date))}}"
                                :mealtime="{{ $recipe->ingestion->id }}"
                                :purchased="false"
                        ></purchases-list>
                    </div>

                    <div class="recipe-detail_panel_line">
                        <span class="recipe-detail_panel_line_label">{{ $recipe->ingestion->title }}</span>
                    </div>

                    <div class="recipe-detail_panel_wrap">
                        <h2 class="recipe-detail_panel_title">@lang('ingredient::common.ingredients')</h2>
                        <x-ingredient-piece-switch></x-ingredient-piece-switch>
                    </div>

                    <ul class="list-unstyled">
                        @foreach($recipe->ingredients as $ingredient)
                            @php
                                $hint = $ingredient->ingredient->hint?->translations->where('locale', $user->lang)->first();
                                $hintData = $hint !== null ? [
                                    'title' => $ingredient->ingredient->name,
                                    'content' => $hint->content,
                                    'link_url' => $hint->link_url,
                                    'link_text' => $hint->link_text,
                                    ] : [];
                            $conversionData = app(IngredientConversionService::class)->generateData($ingredient->ingredient, (int)$ingredient->amount);
                            $isConvertable = $conversionData !== [];
                            @endphp
                            <li data-id="{{ $ingredient->ingredient_id }}" class="d-flex">
                                <div class="ingredient-text js-convert-ingredients"
                                     data-is-convertable="{{ (int)$conversionData }}">
                                        <span class="ingredient-amount-anchor"
                                              data-amount="{{ $ingredient->amount }}"
                                              data-unit="{{$ingredient->ingredient->unit->short_name }}"
                                              @if($isConvertable)
                                                  data-piece="{{$conversionData['ingredient_conversion_amount']}}"
                                              data-fraction-map="{{json_encode($conversionData['fraction_map'], JSON_THROW_ON_ERROR)}}"
                                                @endif>
					                    {{ $ingredient->amount . ' '. $ingredient->ingredient->unit->short_name }}
				                        </span>
                                    <span class="ingredient-title-anchor"
                                          data-text="{{ $ingredient->ingredient->name }}"
                                          @if($isConvertable)
                                              data-text-multiple="{{$conversionData['ingredient_plural_name']}}"
                                          data-alternative-unit="{{$conversionData['ingredient_alternative_unit']}}"
                                        @endif>
                                            {{ $ingredient->ingredient->name }}
                                        </span>
                                    <x-ingredient-tip :data="$ingredient['hint'] ?? []"></x-ingredient-tip>
                                </div>
                                <x-ingredient-tip :data="$hintData"/>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <div class="col-sm-7">
                <div class="row hidden-xs">
                    <div class="col-xs-12">
                        <img src="{{ asset($recipe->image->url('mobile')) }}"
                             alt="{{ $recipe->name }}"
                             width="100%"
                             class="recipe-detail_img img-responsive">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('js/ingredientSwitcher.js') }}"></script>
@endsection

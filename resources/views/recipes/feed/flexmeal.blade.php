@extends('layouts.app')

@section('title', trans('common.ingredients'))

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
                        <img src="{{ asset($recipe->image->url('mobile')) }}" alt="{{ $recipe->name }}" width="100%"
                             class="recipe-detail_img img-responsive">
                    </div>
                </div>

                <!-- Ingredients -->
                <div class="recipe-detail_panel">

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
                    <small class="recipe-detail_panel_title">@lang('common.ingredients')</small>
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
                            @endphp
                            <li data-id="{{ $ingredient['ingredient_id'] }}">
                                {{ $ingredient->amount }} {{ $ingredient->ingredient->unit->short_name }} {{ $ingredient->ingredient->name }}
                                <x-ingredient-tip :data="$hintData"/>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>

            <div class="col-sm-7">
                <div class="row hidden-xs">
                    <div class="col-xs-12">
                        <img src="{{ asset($recipe->image->url('mobile')) }}" alt="{{ $recipe->name }}" width="100%"
                             class="recipe-detail_img img-responsive">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

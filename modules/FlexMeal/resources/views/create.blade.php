@extends('layouts.app')

@section('styles')
    <link href="{{ mix('css/flexmeal.css') }}" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js" defer></script>

    <script>
        window.foodPunk.routes = {
            flexmealSave: '{{route('recipes.flexmeal.save')}}',
            getIngredients: '{{route('ingredients.search.all')}}',
        };
    </script>
@endsection

@section('title', trans('common.flexmeal'))

@section('content')

    <div class="container">
        @include('flexmeal::inc.flexmeal-navigation')

        <h2 class="heading-text">{{ trans('common.meal_header') }}</h2>

        <flex-meal-creation :ingestion-goals="{{Js::from($dietdata)}}"></flex-meal-creation>

        <div class="row">
            <div class="col-xs-12">
                <!-- warning message-->
                <div class="own-recipe_warn">
                    <p>
                        {!!
                            trans(
                                'common.flexmeal_info',
                                [
                                    'TAG1' 		=> '<a href="https://foodpunk.zendesk.com/hc/de/articles/7457434272284-Baukasten-und-FlexMeal" target="_blank">',
                                    'TAG1END' 	=> '</a>',
                                    'TAG2' 		=> '<a href="https://www.youtube.com/watch?v=lxgPDXkk-LQ" target="_blank">',
                                    'TAG2END' 	=> '</a>',
                            ])
                        !!}
                    </p>
                    <div class="icons-identity">
                        <div class="icons-identity-item">
                            <img src="{{ asset("/images/protein.png") }}"
                                 width="25"
                                 class="icons-identity-item-image"
                                 alt="Protein source icon"/>
                            <span> = {{ trans('common.proteins') }};</span>
                        </div>
                        <div class="icons-identity-item">
                            <img src="{{ asset("/images/fett.png") }}"
                                 width="25"
                                 class="icons-identity-item-image"
                                 alt="Fat source icon"/>
                            <span> = {{ trans('common.fats') }};</span>
                        </div>
                        <div class="icons-identity-item">
                            <img src="{{ asset("/images/kohlenhydrate.png") }}"
                                 width="25"
                                 class="icons-identity-item-image"
                                 alt="Carbs source icon"/>
                            <span> = {{ trans('common.carb') }};</span>
                        </div>
                        <div class="icons-identity-item">
                            <img src="{{ asset("/images/kalorien.png") }}"
                                 width="25"
                                 class="icons-identity-item-image"
                                 alt="Calories icon"/>
                            <span> = {{ trans('common.calories') }}.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', trans('common.flexmeal'))

@section('styles')
    <link href="{{  mix('css/flexmeal.css') }}" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js" defer></script>
    <script>
        window.foodPunk.routes = {
            deleteFlexmeal: '{{route('recipes.flexmeal.destroy')}}',
            updateFlexmeal: '{{route('recipes.flexmeal.update')}}',
            getFlexmealsForMealtime: '{{route('recipes.flexmeal.get_for_mealtime')}}',
            getIngredients: '{{route('ingredients.search.all')}}',
        };
    </script>
@endsection

@section('content')
    <div class="container">
        @include('flexmeal::inc.flexmeal-navigation')
        <h2 class="heading-text">{{ trans('common.saved_meal_header') }}</h2>

        @if(empty($dietdata))
            <h2 class="heading-text">{{ trans('common.saved_text') }}</h2>
        @else
            <flex-meal-archive :ingestion-goals="{{Js::from($dietdata)}}"></flex-meal-archive>
            <flex-meal-edit-modal :ingestion-goals="{{Js::from($dietdata)}}"></flex-meal-edit-modal>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="{{ mix('vendor/sweetalert/sweetalert.min.js')}}"></script>
@endsection
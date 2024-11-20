@extends('layouts.print-layout')

@section('title', trans('shopping-list::common.buttons.print_list'))

@section('content')
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        @include('shopping-list::includes.ingredientsList')
    </div>
@endsection
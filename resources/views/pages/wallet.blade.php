@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h1>{{ trans('common.foodpoints') }}</h1>
                <p>{!! trans('common.food_points_page.balance_info',['balance' => \Auth::check() ? \Auth::user()->balance : '0' ]) !!}</p>
                <p>{{ trans('common.food_points_page.about_food_points') }}</p>
                <p>{{ trans('common.food_points_page.earning_options.info') }}</p>
                <ul style="margin-left: 20px">
                    @foreach(range(1, 5) as $i)
                        <li>{{ trans('common.food_points_page.earning_options.option_'.$i) }}</li>
                    @endforeach
                </ul>
                <p>{!! trans('common.food_points_page.purchase_food_points',['link' => 'https://shop.foodpunk.de/collections/gutschein']) !!}</p>
                <p>{!! trans('common.food_points_page.extra_info',['link' => 'mailto:info@foodpunk.de']) !!}</p>
            </div>
        </div>
    </div>
@endsection
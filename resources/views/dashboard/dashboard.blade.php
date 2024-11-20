@extends('layouts.app')

@section('styles')
    <link href="{{ mix('vendor/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ mix('vendor/owlcarousel/assets/owl.theme.default.min.css') }}" rel="stylesheet">
@endsection

@section('title', trans('common.dashboard'))

@section('content')
    <div class="container dashboard">

        @if( !is_null($message) )
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info alert-block">
                        {!! $message !!}
                    </div>
                </div>
            </div>
        @endif

        <div class="main_title">
            <h1>{{ Date::now()->format('l j. F') }}</h1>
            <h2>@lang('common.dashboard_main_title')</h2>
        </div>

            @if( $recipes->isNotEmpty() )
                <section>
                    <div class="mini_title">
                        <h2>{{trans('common.dashboard_sub_title')}}</h2>
                    </div>
                    <div class="recipe-wrapper dashboard_slider owl-carousel owl-theme">
                        @php $date = \Carbon\Carbon::now()->startOfDay()->format('Y-m-d') @endphp
                        @foreach($recipes as $key => $item)
                            <div class="recipe-card">
                                @php
                                    if ($item->creator) {
                                        $bg = $item->image->url('medium');
                                        $routeName = 'recipe.show';
                                    }else {
                                        $bg = $item->originalRecipe->image->url('medium');
                                        $routeName = 'recipe.show.custom.common';
                                    }
                                @endphp
                                <div class="recipe-card_image" style="background-image: url('{{ $bg }}');"
                                     role="presentation"></div>

                                <div class="recipe-card_container">
                                    <a href="{{ route($routeName, [
                                            'id' => $item->id,
                                            'date' => $date,
                                            'ingestion' => $item->pivot->meal_time
                                        ]) }}"
                                       class="recipe-card_image-link"></a>

                                    <div class="recipe-card_info">

                                        @if(isset($item->creator))
                                            <div class="product_name">
                                                <a href="{{ route('recipe.show', [
                                            'id' => $item->id,
                                            'date' => $date,
                                            'ingestion' => $item->pivot->meal_time
                                        ]) }}" class="recipe-card_info_title">{{ $item->title }}</a>

                                                <div class="recipe-title-meal-time">{{ trans('common.'.$item->pivot->meal_time) }}</div>
                                            </div>

                                            <div class="recipe-card_info_favourites">
                                                <favorite
                                                        color="white"
                                                        :recipe="{{ $item->id }}"
                                                        :favorited="{{ is_null($item->favorite) ? 'false' : 'true' }}"
                                                ></favorite>
                                            </div>

                                        @elseif(is_null($item->recipe_id))
                                            <div class="product_name">
                                                <a href="{{ route('recipe.show.custom', $item->id) }}"
                                                   class="recipe-card_info_title">
                                                    {{ $item->title }}
                                                </a>
                                                <div class="recipe-title-meal-time">{{ trans('common.'.$item->pivot->meal_time) }}</div>
                                            </div>
                                        @else
                                            <div class="product_name">
                                                <a href="{{ route('recipe.show.custom.common',[
                                            'id' => $item->id,
                                            'date' => parseDateString($item->pivot->meal_date),
                                            'ingestion' => $item->pivot->meal_time
                                        ]) }}"
                                                   class="recipe-card_info_title">
                                                    {{ $item->originalRecipe->title.' *'.trans('common.edited').'*' }}
                                                </a>

                                                <div class="recipe-title-meal-time">{{ trans('common.'.$item->pivot->meal_time) }}</div>
                                            </div>

                                            <div class="recipe-card_info_favourites">
                                                <favorite
                                                        color="white"
                                                        :recipe="{{ $item->originalRecipe->id }}"
                                                        :favorited="{{ is_null($item->originalRecipe->favorite) ? 'false' : 'true' }}"
                                                ></favorite>
                                            </div>
                                        @endif

                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
            <nav class="dashboard_links">
            <ul>
                <li>
                    <a href="{{ route('posts.list') }}"
                       style="background-image: url({{asset('/images/icons/db_diary_add.svg')}});"
                       title="{{trans('common.posts')}}"
                       aria-label="{{trans('common.posts')}}"
                    ></a>
                </li>
                <li>
                    <a href="{{ route('diary.statistics') }}"
                       style="background-image: url({{asset('/images/icons/db_add_statistics.svg')}});"
                       title="{{trans('common.diary_statistics')}}"
                       aria-label="{{trans('common.diary_statistics')}}"
                    ></a>
                </li>
                <li>
                    <a href="{{ route('recipes.flexmeal') }}"
                       style="background-image: url({{asset('/images/icons/db_calculator.svg')}});"
                       title="{{trans('common.flexmeal')}}"
                       aria-label="{{trans('common.flexmeal')}}"
                    ></a>
                </li>
                <li>
                    <a href="{{ route('recipes.all.get') }}"
                       style="background-image: url({{asset('/images/icons/db_search.svg')}});"
                       title="{{trans('common.all_recipes')}}"
                       aria-label="{{trans('common.all_recipes')}}"
                    ></a>
                </li>
            </ul>
            </nav>

        <div class="clearfix"></div>

            @if((isset($courseArticle) && !is_null($courseArticle)) || !is_null($customArticle) )
                <section class="dashboard_news">
                    <x-daily-course-article :article="$courseArticle"
                                            :course-data="(array)$aboChallengeData"></x-daily-course-article>
                    <x-custom-course-article :article="$customArticle"></x-custom-course-article>
                </section>
        @endif
    </div>
@endsection

@section('scripts')
    @if( $recipes->isNotEmpty() )
        <script src="{{ mix('vendor/owlcarousel/owl.carousel.min.js') }}"></script>
        <script>
            $(document).ready(function () {
                $('.owl-carousel').owlCarousel({
                    items: 1,
                    margin: 10,
                    nav: false,
                    dots: true,
                    autoHeight: true,
                    slideBy: 1,
                    touchDrag: true,
                    mouseDrag: true,
                });
            });
        </script>
    @endif
@append

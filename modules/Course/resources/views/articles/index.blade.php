@extends('layouts.app')

@section('title', trans('course::common.lessons'))

@section('styles')
    <link rel="stylesheet" href="{{ mix('css/articles.css') }}">
@endsection

@section('content')
    <div class="container">
        @if( empty($courseArticles) )
            <p>
                @lang('course::common.no_courses')
                <a href="{{ route('course.buy') }}">@lang('course::common.click')</a>
            </p>
        @else
            <div class="row">
                <div class="col-md-4">
                    <ul class="nav nav-pills nav-stacked">
                        @foreach($courseArticles as $articleData)
                            <x-article-nav-link :course-article-data="$articleData" :is-active="$loop->first">
                            </x-article-nav-link>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-8">
                    <div class="tab-content search-recipes_list articles">
                        @foreach($courseArticles as $articleData)
                            <x-article-nav-content :course-article-data="$articleData" :is-active="$loop->first">
                            </x-article-nav-content>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

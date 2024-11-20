@extends('layouts.app')

@section('title', trans('course::common.articles') . $title)

@section('content')
    <div class="container">
        @if( empty($article) )
            <h1>@lang('course::common.article_not_available')</h1>
        @else
            <h1>{!! $article['post_title'] ?? '' !!}</h1>
            <div>{!! $article['post_content'] ?? '' !!}</div>
        @endif
    </div>
@endsection

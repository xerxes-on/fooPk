@extends('layouts.errors')
{{--TODO: need translations--}}
@section('content')
    <div class="not-found text-center">
        <h1 class="text-center">NO RESULTS FOUND</h1>
        <p class="lead">The requested page could not be found. Refine your search or use the navigation above to find
            the post.</p>
        <a class="btn btn-tiffany" href="{{ url()->previous() }}">Back</a>
    </div>
@endsection

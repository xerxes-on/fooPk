@extends('layouts.app')

@section('title', trans('common.formular.title'))

@section('styles')
    <link href="{{ mix('css/questionnaire.css') }}" rel="stylesheet">
@endsection

@section('content')
    <questionnaire></questionnaire>
@endsection
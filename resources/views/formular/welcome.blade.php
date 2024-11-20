@extends('layouts.app')

@section('title', trans('common.formular.title'))

@section('content')

    <div class="formular_start-panel">
        <div class="container">
            <div class="panel_logo"></div>
            <h2 class="panel_title">In 10 Schritten zu deinem persönlichen Ernährungsplan!</h2>
            <div class="panel_text">
                <p>Beantworte unseren Fragebogen, damit wir deinen Ernährungsplan individuell an dich anpassen
                    können!</p>
            </div>
            <a href="{{ route('formular.tryForFree') }}" class="panel_start">Los geht’s!</a>
        </div>
    </div>

@endsection
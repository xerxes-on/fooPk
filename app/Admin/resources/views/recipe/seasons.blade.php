@section('seasons')
    <div>
        <h4>{{ trans('common.season') }}</h4>
        <div id="recipe_steps_container">
            @if(!is_null($seasons))
                <ul>
                    @foreach($seasons as $season)
                        <li>
                            {{ $season['name'] }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
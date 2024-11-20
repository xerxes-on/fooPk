<div id="challenge" class="tab-pane fade in col-xs-12">
    <div class="row">
        <div class="col-xs-12">
            @if(!empty($goal))
                <h1>@lang('common.goal'):</h1>
                <p class="lead">{{ $goal }}</p>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <h2>@lang('common.nutrients'):</h2>

            @if(!empty($dietdata))

                <div class="content-panel">
                    <h3 class="content-panel_title">@lang('common.general'):</h3>
                    <ul>
                        <li>{{ trans('common.calories').': '. $dietdata['Kcal'] }}</li>
                        <li>{{ trans('common.carbohydrates').': '. $dietdata['KH'] }}</li>
                        <li>{{ trans('common.protein').': '. $dietdata['EW'] }}</li>
                        <li>{{ trans('common.fat').': '. $dietdata['F'] }}</li>
                    </ul>
                </div>

                @foreach($dietdata['ingestion'] as $meal_time => $information)
                    <div class="content-panel">
                        <h3 class="content-panel_title">{{ trans('common.'. $meal_time) }}:</h3>
                        <ul>
                            <li>{{ trans('common.calories').': '. $information['Kcal'] }}</li>
                            <li>{{ trans('common.carbohydrates').': '. $information['KH'] }}</li>
                            <li>{{ trans('common.protein').': '. $information['EW'] }}</li>
                            <li>{{ trans('common.fat').': '. $information['F'] }}</li>
                        </ul>
                    </div>
                @endforeach
            @else
                <p>@lang('common.empty_nutrients')</p>
            @endif
        </div>
    </div>
</div>

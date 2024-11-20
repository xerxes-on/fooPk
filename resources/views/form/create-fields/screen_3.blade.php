<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <label class="control-label formular_panel_category">{{ trans('survey_questions.screen_2_title') }}</label>

    <div class="form-main-metric weight opacity_field form-group{{ $_qWeight->required ? ' required' : '' }}">
        {!! Form::label($_qWeight->id . '[answer]', trans('survey_questions.' . $_qWeight->key_code), ['class' => 'control-label formular_panel_category']) !!}

        {!! Form::number($_qWeight->id . '[answer]', null, [
                'required' => (bool)$_qWeight->required,
                'class' => 'form-control formular_panel_input',
                'placeholder' => trans('survey_questions.weight_placeholder'),
                'data-rule-number' => 'true'
        ]) !!}
    </div>

    <div class="form-main-metric growth form-group{{ $_qGrowth->required ? ' required' : '' }}">
        {!! Form::label($_qGrowth->id . '[answer]', trans('survey_questions.' . $_qGrowth->key_code), ['class' => 'control-label formular_panel_category']) !!}

        {!! Form::number($_qGrowth->id . '[answer]', null, [
                'required' => (bool)$_qGrowth->required,
                'class' => 'form-control formular_panel_input',
                'placeholder' => trans('survey_questions.growth_placeholder'),
                'data-rule-digits' => 'true'
        ]) !!}
    </div>

    @php
        $rangeClass = $_qFatPercentage->key_code . '_' . $_qFatPercentage->id;
        $options    = $_qFatPercentage->options;
    @endphp

    <div class="form-main-metric  form-group{{ $_qFatPercentage->required ? ' required' : '' }} percentage-bar"
         style="padding-bottom: 30px;">
        <div class="range_sports_item" style="padding: 0px; margin: 0px;">
            <div class="range_sports_info" style="margin: -15px -15px 0px 0px"> ?
                <span>{{ trans('survey_questions.'. $_qFatPercentage->key_code .'_tooltip') }}</span></div>
        </div>
        {!! Form::label($_qFatPercentage->id . '[answer]', trans('survey_questions.'.$_qFatPercentage->key_code), ['class' => 'control-label formular_panel_category']) !!}

        <input type="text" class="{{ $rangeClass }} formular_panel_input" name="{{ $_qFatPercentage->id }}[answer]"
               value=""/>

    </div>

</div>

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('.{{ $rangeClass }}').ionRangeSlider({
                //grid: true,

                @if(key_exists('min', $options))
                min: "{{ $options['min'] }}",
                @endif

                        @if(key_exists('max', $options))
                max: "{{ $options['max'] }}",
                @endif

                        @if(key_exists('step', $options))
                step: "{{ $options['step'] }}",
                @endif

                        @if(key_exists('from', $options))
                from: "{{ $options['from'] }}",
                @endif

                        @if(key_exists('values', $options))
                values: [{{ implode(', ', $options['values']) }}],
                @endif
            });
        });
    </script>
@append
@php
    $rangeClass = $_question->key_code . '_' . $_question->id;

    $count = 0;
    $time = 0;
    $options = $_question->options;

    if ( isset($answer) && key_exists($_question->id, $answer) ) {
        $currentAnswer = json_decode($answer[$_question->id]['answer']);

        $count = (int)$currentAnswer->count;
        $time = (int)$currentAnswer->time;
    }
@endphp

<div class="form-group{{ $_question->required ? ' required' : '' }}" data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

    <div class="row range_sports_wrapper">
        <div class="col-xs-4 col-md-2">
            <select name="{{ $_question->id }}[answer][count]" class="form-control formular_panel_select">
                @for($i = 0; $i <= 7; $i++)
                    <option value="{{ $i }}" {{ $count === $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="col-xs-8 col-md-10">
            <input type="text" class="{{ $rangeClass }} formular_panel_input" name="{{ $_question->id }}[answer][time]"
                   value="{{ $time }}"/>
        </div>
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
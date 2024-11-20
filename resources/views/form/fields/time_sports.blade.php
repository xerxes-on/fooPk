@php
    $rangeClass = $_question->key_code . '_' . $_question->id;

    $time = $hours = $minutes = $count = 0;
    $options = $_question->options;

    if ( isset($answer) && key_exists($_question->id, $answer) ) {
        $currentAnswer = json_decode($answer[$_question->id]['answer']);

        $count = (int)$currentAnswer->count;
        $time = (int)$currentAnswer->time;

        $hours = floor($time / 60);
        $minutes = ($time % 60);
    }
@endphp

<div class="form-group{{ $_question->required ? ' required' : '' }}" data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

    <div class="row range_sports_edit_wrapper form-inline">
        <div class="col-md-12">
            <select name="{{ $_question->id }}[answer][count]" class="form-control formular_panel_select"
                    style="margin-right: 20px;">
                @for($i = 0; $i <= 7; $i++)
                    <option value="{{ $i }}" {{ $count === $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
            <span class="time-count">Einheiten pro Woche</span>
        </div>

        <div class="col-md-12">
            <span class="time-count right">für je </span>
            <select name="{{ $_question->id }}[answer][time]" class="form-control formular_panel_select"
                    style="margin: 0 20px;">
                @for($i = 0; $i <= 100; $i += 5)
                    <option value="{{ $i }}" {{ $time === $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
            <span class="time-count">Minuten</span>
        </div>
    </div>
</div>

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('.{{ $rangeClass }}').on('change', function () {
                let timeArr = $(this).val().split(':'),
                    minutes = parseInt(timeArr[0]) * 60 + parseInt(timeArr[1]);

                $('input[name="{{ $_question->id }}[answer][time]"]').val(minutes);
            });
        });
    </script>
@append
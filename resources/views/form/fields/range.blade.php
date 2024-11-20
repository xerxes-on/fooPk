@php
    $rangeClass = $_question->key_code . '_' . $_question->id;
    $options    = $_question->options;
    if ( isset($answer) && key_exists($_question->id, $answer) ) $options['from'] = $answer[$_question->id]['answer'];
@endphp

<div class="form-group{{ $_question->required ? ' required' : '' }}" data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

    <input type="text" class="{{ $rangeClass }} formular_panel_input" name="{{ $_question->id }}[answer]" value=""/>
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

                        @if($_question->key_code === 'age')
                onFinish: function (data) {
                    if (data.from < 16) {
                        alert(
                            'Solltest du unter 16 Jahren sein, benötigen wir von dir für die Buchung des Programms das Einverständnis der Eltern.');
                    }
                },
                @endif
            });
        });
    </script>
@append
{{--todo: remove as new formular is done--}}
<div class="form-group{{ $_question->required ? ' required' : '' }}"
     @if(!is_null(\Auth::user()) && \Auth::user()->role !== 1 && \Auth::user()->isFormularExist() && $_question->key_code !== 'age') style="display: none;"
     @endif  data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.' . $_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

    <div class="input-group date"
         data-provide="datepicker"
         data-date-format="dd.mm.yyyy"

         @if($_question->key_code !== 'age')
             data-date-start-date="@if(!(in_array(\Auth::user()->role, [1, 5]))) {{ \Carbon\Carbon::now()->addDays(4)->format('d.m.Y') }} @endif"
         @endif

         data-date-autoclose="false"
         data-date-today-highlight="true"
         data-date-week-start="1"
         data-date-language="{{ app()->getLocale() }}"
         id="{{ $_question->key_code }}"
    >

        @php $currentAnswer = ( isset($answer)
                                && key_exists($_question->id, $answer)
                                && (strpos($answer[$_question->id]['answer'], '.') !== false || strpos($answer[$_question->id]['answer'], '-') !== false)
                              ) ? \Date::parse($answer[$_question->id]['answer'])->format('d.m.Y') : ''; @endphp

        {!! Form::text($_question->id . '[answer]', $currentAnswer, [
            'required' => (bool)$_question->required,
            'class' => 'form-control formular_panel_input',
            'placeholder' => 'dd.mm.yyyy',
            'autocomplete' => 'off',
        ]) !!}

        <div class="input-group-addon formular_panel_calendar">
            <span class="glyphicon glyphicon-calendar"></span>
        </div>
    </div>
</div>

@if($_question->key_code === 'age')
    @section('scripts')
        <script type="text/javascript">
            $(document).ready(function () {
                $('#age').on('changeDate', function (e) {
                    let selectedDate = new Date(e.date.toString());
                    checkAge(selectedDate);
                });

                $('input[name="{{ $_question->id }}[answer]"]').on('change', function () {
                    let parts = this.value.split('.');
                    let selectedDate = new Date(parseInt(parts[2], 10), parseInt(parts[1], 10) - 1, parseInt(parts[0], 10));
                    checkAge(selectedDate);
                });
            });

            function checkAge(selectedDate) {
                let currentDate = new Date();
                let age = currentDate.getFullYear() - selectedDate.getFullYear();
                let m = currentDate.getMonth() - selectedDate.getMonth();

                if (m < 0 || (m === 0 && currentDate.getDate() < selectedDate.getDate())) age--;

                if (age < 16) {
                    $('#age').datepicker('update', '');
                    alert(
                        'Solltest du unter 16 Jahren sein, benötigen wir von dir für die Buchung des Programms das Einverständnis der Eltern.');
                }
            }
        </script>
    @append
@endif
@php
    // todo: remove as new formular is finished
        use App\Enums\Admin\Permission\RoleEnum;
        $user          = $client ?? Auth::user();
        $currentUser = Auth::user()??$user;
        $currentAnswer = (
            isset($answer) &&
            key_exists($_question->id, $answer) &&
            (
                str_contains($answer[$_question->id]['answer'], '.') ||
                str_contains($answer[$_question->id]['answer'], '-')
            )
        ) ?
            \Date::parse($answer[$_question->id]['answer'])->format('d.m.Y') :
            '';
@endphp
<div class="form-group{{ $_question->required ? ' required' : '' }}"
     @if(!is_null($user) && $currentUser->role_name === RoleEnum::USER->value && $user->isFormularExist() && $_question->key_code !== 'age')
         style="display: none;"
     @endif  data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.' . $_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

    <div class="input-group date"
         data-provide="datepicker"
         data-date-format="dd.mm.yyyy"
         @if($_question->key_code !== 'age')
             data-date-start-date="@if(!is_null($currentUser) && !(in_array($currentUser->role_name, [RoleEnum::ADMIN->value, RoleEnum::CONSULTANT->value, RoleEnum::CUSTOMER_SUPPORT->value]))) {{ \Carbon\Carbon::now()->addDays(4)->format('d.m.Y') }} @endif"
         @endif

         data-date-autoclose="false"
         data-date-today-highlight="true"
         data-date-week-start="1"
         data-date-language="{{ app()->getLocale() }}"
         id="{{ $_question->key_code }}"
    >

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
                    alert("{{ trans('survey_questions.under_16') }}");
                }
            }
        </script>
    @append
@endif

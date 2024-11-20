<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <div class="form-group-radio form-radio-main-target gender-chose form-group{{ $_qGender->required ? ' required' : '' }}">
        {!! Form::label($_qGender->id . '[answer]', trans('survey_questions.'.$_qGender->key_code), ['class' => 'control-label formular_panel_category']) !!}

        @foreach($_qGender->options as $key => $value)
            <div class="radio {{ $key }}">
                <label class="formular_panel_label {{ $key }}" for="{{ $key }}">
                    <div class="label-desc">
                        @if($key == 'no_matter')
                            {{ trans("survey_questions.$_qGender->key_code".'_'.$key) }}
                        @else
                            {{ trans('survey_questions.'.$key) }}
                        @endif
                    </div>
                </label>
                <input name="{{ $_qGender->id }}[answer]"
                       type="radio"
                       value="{{ $key . '|' . $value }}"
                       id="{{ $key }}"
                        {{ ($_qGender->required && $key == 0) ? ' required' : '' }}
                />
            </div>
        @endforeach
    </div>


    <div class="form-radio-main-date form-group{{ $_qAge->required ? ' required' : '' }}">
        {!! Form::label($_qAge->id . '[answer]', trans('survey_questions.' . $_qAge->key_code), ['class' => 'control-label formular_panel_category']) !!}
        <div class="main-date-inner">
            <div class="input-group date" id="{{ $_qAge->key_code }}">

                {!! Form::text($_qAge->id . '[answer]', null, [
                    'required' => (bool)$_qAge->required,
                    'class' => 'form-control formular_panel_input',
                    'placeholder' => 'dd.mm.yyyy',
                    'autocomplete' => 'off',
                    'id' => 'date-of-birth'
                ]) !!}

                <div class="input-group-addon formular_panel_calendar">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>
        </div>
    </div>

</div>
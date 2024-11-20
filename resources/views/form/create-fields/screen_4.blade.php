<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <div class="form-group-radio activity form-radio-main-target form-group{{ $_question->required ? ' required' : '' }}">
        {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

        @foreach($_question->options as $key => $value)
            <div class="radio {{ $key }}">
                <label class="formular_panel_label {{ $key }}" for="{{ $key }}">
                    <div class="label-desc">
                        @if($key == 'no_matter')
                            {{ trans("survey_questions.$_question->key_code".'_'.$key) }}
                        @else
                            {{ trans('survey_questions.'.$key) }}
                        @endif
                    </div>
                </label>
                <input name="{{ $_question->id }}[answer]"
                       type="radio"
                       value="{{ $key . '|' . $value }}"
                       id="{{ $key }}"
                        {{ ($_question->required && $key == 0) ? ' required' : '' }}
                />
            </div>
        @endforeach
    </div>

</div>
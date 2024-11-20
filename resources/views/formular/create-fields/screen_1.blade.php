<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <div class="form-group-radio first-panel form-radio-main-target opacity form-group{{ $_question->required ? ' required' : '' }}">
        {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}
        <p>{{ trans('survey_questions.screen_1_description') }}</p>
        @foreach($_question->options as $key => $value)
            <div class="radio {{ $key }}">
                <label class="formular_panel_label {{ $key }}" for="{{ $key }}">
                    <span class="label-desc">
                        @if($key == 'no_matter')
                            {{ trans("survey_questions.$_question->key_code".'_'.$key) }}
                        @else
                            {{ trans('survey_questions.'.$key) }}
                        @endif
                    </span>
                </label>
                <input name="{{ $_question->id }}[answer]"
                       type="radio"
                       value="{{ $key . '|' . $value }}"
                       id="{{ $key }}"
                        {{ $_question->required ? ' required' : '' }}
                />
            </div>
        @endforeach
    </div>

</div>

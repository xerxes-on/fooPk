@php
    // Hide daily_routine question for editing
    if($_question->key_code === 'daily_routine') {return;}
@endphp
<div class="form-group-radio form-group{{ $_question->required ? ' required' : '' }}" data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

    @php
        $currentAnswer = ( isset($answer) && key_exists($_question->id, $answer) ) ? json_decode($answer[$_question->id]['answer'], true) : '';
        $currentAnswer = !empty($currentAnswer) ? array_keys($currentAnswer)[0] : '';
    @endphp

    <div class="clearfix"></div>
    @foreach($_question->options as $key => $value)
        @if(is_array($_question->attributes) && key_exists('inline', $_question->attributes) && $_question->attributes['inline'])
            <div class="radio-inline">
                @else
                    <div class="radio">
                        @endif
                        <label class="formular_panel_label">
                            <input name="{{ $_question->id }}[answer]"
                                   type="radio"
                                   value="{{ $key . '|' . $value }}"
                                    {{ ($_question->required && $key == 0) ? ' required' : '' }}
                                    {{ $key === $currentAnswer ? 'checked' : '' }}
                            />
                            @if($key == 'no_matter')
                                {{ trans("survey_questions.$_question->key_code".'_'.$key) }}
                            @else
                                {{ trans('survey_questions.'.$key) }}
                            @endif
                        </label>
                    </div>
                    @endforeach
            </div>

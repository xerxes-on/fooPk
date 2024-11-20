<div class="screen-wrapper" data-key="{{ $_dataKey }}">
    @php
        //todo: remove as new formular is done
            $user = auth()->user();
    @endphp
    <div class="form-group-radio activity form-radio-main-target form-group{{ $_question->required ? ' required' : '' }}"
         @if(!is_null($user) && $user->role !== 1 && $user->isFormularExist()) style="display: none;" @endif>
        {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

        @foreach($_question->options as $key => $value)
            <div class="radio">
                @if($value !== 'no_matter')
                    <div class="range_sports_item" style="padding: 0; margin: 0;">
                        <div class="range_sports_info"> ?
                            <span>{{ trans("survey_questions.{$value}_tooltip") }}</span>
                        </div>
                    </div>
                @endif
                <label class="formular_panel_label" for="{{  $key . '_' . $value }}">
                    <span class="label-desc">
                        {{ trans('survey_questions.'. ($value === 'no_matter' ? "{$_question->key_code}_$value": $value)) }}
                    </span>

                </label>
                <input name="{{ $_question->id }}[answer]"
                       type="radio"
                       value="{{ $value }}"
                       id="{{  $key . '_' . $value }}"
                        {{ $_question->required ? ' required' : '' }}
                />
            </div>
        @endforeach
    </div>
</div>

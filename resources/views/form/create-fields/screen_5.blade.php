<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <label class="control-label formular_panel_category">{{ trans('survey_questions.screen_5_title') }}</label>

    @foreach(compact('_qIntensiveSports', '_qModerateSports', '_qLightSports') as $_question)
        <div class="form-group{{ $_question->required ? ' required' : '' }} range_sports_outer">
            <div class="range_sports_item {{ $_question->key_code . '_' . $_question->id }}">
                <div class="control-label formular_panel_category">{{ trans('survey_questions.'. $_question->key_code) }}</div>
                <div class="range_sports_info">?
                    <span>{{ trans('survey_questions.'. $_question->key_code .'_tooltip') }}</span>
                </div>

                <div class="select_wrap">
                    <select name="{{ $_question->id }}[answer][count]" class="form-control formular_panel_select"
                            id="[answer][count]">
                        @for($i = 0; $i <= 7; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <span class="time-count">Einheiten pro Woche</span>
                </div>
                <div class="select_wrap">
                    <span class="time-count right">für je </span>
                    <select name="{{ $_question->id }}[answer][time]" class="form-control formular_panel_select"
                            id="[answer][time]">
                        @for($i = 0; $i <= 100; $i += 5)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <span class="time-count">Minuten</span>
                </div>
            </div>
        </div>
    @endforeach

</div>

<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <div class="form-radio-main-date form-group{{ $_qDateStart->required ? ' required' : '' }}">
        {!! Form::label($_qDateStart->id . '[answer]', trans('survey_questions.' . $_qDateStart->key_code), ['class' => 'control-label formular_panel_category']) !!}
        <h4>{{ trans('survey_questions.screen_9_question_description') }}</h4>
        <div class="main-date-inner">
            <div class="input-group date" id="{{ $_qDateStart->key_code }}">

                {!! Form::text($_qDateStart->id . '[answer]', null, [
                    'required' => (bool)$_qDateStart->required,
                    'class' => 'form-control formular_panel_input',
                    'placeholder' => 'dd.mm.yyyy',
                    'autocomplete' => 'off',
                    'id' => 'date-start'
                ]) !!}

                <div class="input-group-addon formular_panel_calendar">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group{{ $_question->required ? ' required' : '' }}">
        <label class="control-label formular_panel_category">{{ trans('survey_questions.' . $_question->key_code) }}</label>
        <p>{{ trans('survey_questions.screen_9_description') }}</p>

        <div class="form-group{{ $_question->required ? ' required' : '' }} formulare-textarea">
            <textarea class="form-control formular_panel_textarea rounded-0"
                      name="{{ $_question->id }}[answer]"
                      placeholder="{{ trans('survey_questions.any_comments_placeholder') }}"
                      rows="5">
            </textarea>
        </div>
    </div>

</div>
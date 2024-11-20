<div class="form-group{{ $_question->required ? ' required' : '' }}" data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.' . $_question->key_code), ['class' => 'control-label formular_panel_category']) !!}
    {!! Form::number(
        $_question->id . '[answer]',
        ( isset($answer) && key_exists($_question->id, $answer) ) ? $answer[$_question->id]['answer'] : '',
        [
            'required' => (bool)$_question->required,
            'class' => 'form-control formular_panel_input'
        ]
    ) !!}
</div>

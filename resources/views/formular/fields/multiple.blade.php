@php
    // TODO: element is invalid, need to develop logic
@endphp
<div class="form-group{{ $_question->required ? ' required' : '' }}" data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.' . $_question->key_code), ['class' => 'control-label formular_panel_category']) !!}
    {!! Form::select(
        $_question->id . '[answer][]',
        $_question->options,
        //[null => '--- Please Select ---'] + $_question->options,
        null,
        [
            'required' => (bool)$_question->required,
            'class' => 'form-control select2 formular_panel_select',
            'multiple' => 'multiple'
        ]
    ) !!}
</div>
@php $currentAnswer = ( isset($answer) && key_exists($_question->id, $answer) ) ? $answer[$_question->id]['answer'] : ''; @endphp

<div class="form-group{{ $_question->required ? ' required' : '' }}" data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.' . $_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

    <textarea class="form-control formular_panel_textarea rounded-0"
              name="{{ $_question->id }}[answer]"
              rows="5">{{ $currentAnswer }}
    </textarea>
</div>
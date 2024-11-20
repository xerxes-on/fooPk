<div class="form-group{{ $question['is_required'] ? ' required' : '' }}  @error($question['slug']) has-error @enderror"
     id="question_{{$question['slug']}}"
     data-key="{!! ($question['id']) !!}">
    <label class="form-check-label" for="{{$question['slug']}}">{{$question['title']}}</label>
    <div class="form-input">
        <input class="form-control js-validate-number-input"
               type="number"
               name="{{$question['slug']}}"
               id="{{$question['slug']}}"
               step=".1"
               value="{{$question['answer'] ?? old($question['slug'])}}">
    </div>
</div>
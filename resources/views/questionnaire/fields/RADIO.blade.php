@php
    $answer = $question['answer'] ?? old($question['slug'], '');
@endphp
<fieldset id="question_{{$question['slug']}}"
          class="form-group{{ $question['is_required'] ? ' required' : '' }}  @error($question['slug']) has-error @enderror"
          data-key="{{($question['id'])}}">
    <legend class="">{{$question['title']}}</legend>

    <div class="row">
        @foreach($question['options'] as $data)
            <div class="col-sm-6">
                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           name="{{$question['slug']}}"
                           id="{{$question['slug'].'_'.$data['key']}}"
                           value="{{$data['key']}}"
                            @checked($answer === $data['key'])>
                    <label class="form-check-label" for="{{$question['slug'].'_'.$data['key']}}">
                        {{$data['value']}}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</fieldset>
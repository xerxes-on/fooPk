<fieldset
        class="form-group{{ $question['is_required'] ? ' required' : '' }}  @error($question['slug']) has-error @enderror"
        id="question_{{$question['slug']}}"
        data-key="{{($question['id'])}}">
    <legend>{{$question['title']}}</legend>
    <div class="row">
        @php
            $prefix     = '';
            $subKey     = '';
            $tagOpened  = false; // flag to check if tag is opened, required for grouping subfields
            $answerKeys = array_keys($question['answer'] ?? []);
        @endphp

        @foreach($question['options'] as $data)
            @php
                // checking whether the key contains underscore, if yes, then it is a subfield
                if (str_contains($data['key'], '_')) {
                    $subKey = substr($data['key'], strripos($data['key'], '_') + 1);
                } else {
                    $prefix = $data['key'];
                }
                $subAnswer = $question['answer'][$prefix][$subKey] ?? '';
            @endphp
            {{-- render subfields --}}
            @if($subKey !== '')
                @if($tagOpened ===false)
                    <div class="col-sm-12 js-{{$question['slug'] . '-'.$prefix}}"
                         @if($subAnswer === '') style="display: none"@endif>
                        <div class="row">
                            @php $tagOpened = true;@endphp
                            @endif
                            <div class="col-sm-12">
                                <label class="form-check-label" for="{{$question['slug'].$data['key']}}">
                                    {{$data['value']}}
                                </label>
                                <div class="form-input">
                                    <input class="form-control js-validate-number-input js-{{$question['slug'] . '-'.$subKey}}"
                                           type="number"
                                           name="{{$question['slug']}}[{{$prefix}}][{{$subKey}}]"
                                           id="{{$question['slug'].$data['key']}}"
                                           value="{{$subAnswer}}"
                                    >
                                </div>
                            </div>
                            @php $subKey = '';@endphp
                            @continue
                            @endif
                            @if($tagOpened ===true)
                        </div>
                    </div>
                    @php $tagOpened = false;@endphp
                @endif
                {{-- render main fileds --}}
                <div class="col-sm-12">
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               name="{{$question['slug']}}[]"
                               id="{{$question['slug'].'_'.$data['key']}}"
                               value="{{$data['key']}}"
                                @checked(in_array($data['key'], $answerKeys))>
                        <label class="form-check-label" for="{{$question['slug'].'_'.$data['key']}}">
                            {{$data['value']}}
                        </label>
                    </div>
                </div>
                @endforeach
    </div>
</fieldset>
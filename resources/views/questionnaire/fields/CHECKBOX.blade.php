<fieldset
        class="form-group{{ $question['is_required'] ? ' required' : '' }} @error($question['slug']) has-error @enderror"
        id="question_{{$question['slug']}}"
        data-key="{{$question['id']}}">
    <legend>{{$question['title']}}</legend>

    <div class="row">
        @foreach($question['options'] as $data)
            @php
                $answer = $question['answer'] ?? old($question['slug'], []);
                // Prepare exclusion rules
                if (!empty($data['exclude'])) {
                    $exclude = implode(';', $data['exclude']);
                }
                // set other option to array to be able to check it
                if (in_array(\App\Services\Questionnaire\Question\BaseQuestionService::OTHER_OPTION_SLUG, array_keys($answer))) {
                    $answer[] = \App\Services\Questionnaire\Question\BaseQuestionService::OTHER_OPTION_SLUG;
                }
                // Check if other option present
                $hasOtherOption = $data['key'] === \App\Services\Questionnaire\Question\BaseQuestionService::OTHER_OPTION_SLUG;
                if ($hasOtherOption) {
                    $otherAnswer = $question['answer']['other'] ?? old($question['slug'], '');
                    if (!empty($otherAnswer['other'])) {
                        $otherAnswer = $otherAnswer['other'];
                    }
                    if (is_array($otherAnswer)) {
                        $otherAnswer = $otherAnswer['other'] ?? '';
                    }
                    // We need to prevent other option to be checked in case its value is empty
                    if (empty($otherAnswer)) {
                        foreach ($answer as $key => $value) {
                            if ($value === \App\Services\Questionnaire\Question\BaseQuestionService::OTHER_OPTION_SLUG) {
                                unset($answer[$key]);
                            }
                        }
                    }
                }
            @endphp
            <div class="col-sm-6">
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="{{$question['slug']}}[{{$data['key']}}]"
                           id="{{$question['slug'].'_'.$data['key']}}"
                           @if(!empty($exclude)) data-exclude="{{$exclude}}" @endif
                           value="{{$data['key']}}"
                            @checked(in_array($data['key'],$answer))>
                    <label class="form-check-label" for="{{$question['slug'].'_'.$data['key']}}">
                        {{$data['value']}}
                    </label>
                </div>
            </div>
            @if($hasOtherOption)
                <div class="col-sm-12 js-{{$question['slug']}}-other" @style(['display: none' => empty($otherAnswer)])>
                    <div class="form-group">
                        <input type="text"
                               class="form-control"
                               id="{{$question['slug'].'_'.$data['key']}}_text"
                               name="{{$question['slug']}}[other]"
                               value="{{$otherAnswer}}">
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</fieldset>
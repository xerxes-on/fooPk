@php
    $currentAnswer = [];
    $other = false;

    if ( isset($answer) && key_exists($_question->id, $answer) ) {
        $val = json_decode($answer[$_question->id]['answer']);
        $currentAnswer = array_keys(json_decode($answer[$_question->id]['answer'], true) ?? []);

        if (in_array($_question->attributes['show_textarea'], $currentAnswer)) {
            $other = $val->{$_question->attributes['show_textarea']};
        }
    }

@endphp
<div class="form-group" data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

    @foreach($allergyTypes->where('name', $_question->key_code)->first()->allergies as $value)
        <div class="checkbox cntr">
            <label class="formular_panel_label label-cbx">
                <input name="{{ $_question->id }}[answer][{{ $value->slug }}]"
                       type="checkbox"
                       value="{{ $value->name }}"
                       class="invisible"
                        {{ in_array($value->slug, $currentAnswer) ? 'checked' : '' }}
                />

                {{-- TODO: div should not be used here --}}
                <div class="checkbox-svg">
                    <svg width="20px" height="20px" viewBox="0 0 20 20">
                        <path d="M3,1 L17,1 L17,1 C18.1045695,1 19,1.8954305 19,3 L19,17 L19,17 C19,18.1045695 18.1045695,19 17,19 L3,19 L3,19 C1.8954305,19 1,18.1045695 1,17 L1,3 L1,3 C1,1.8954305 1.8954305,1 3,1 Z"></path>
                        <polyline points="4 11 8 15 16 6"></polyline>
                    </svg>
                </div>

                <span>{{ $value->name }}</span>
            </label>
        </div>
    @endforeach

    <div class="checkbox cntr">
        <label class="formular_panel_label label-cbx">
            <input name="{{ $_question->id }}[answer][{{ $_question->attributes['show_textarea'] }}]"
                   id="show_textarea_{{ $_question->id }}"
                   type="checkbox"
                   value="{{ $_question->attributes['show_textarea'] }}"
                   class="invisible"
                    {{ $other ? 'checked' : '' }}
            />

            {{-- TODO: div should not be used here --}}
            <div class="checkbox-svg">
                <svg width="20px" height="20px" viewBox="0 0 20 20">
                    <path d="M3,1 L17,1 L17,1 C18.1045695,1 19,1.8954305 19,3 L19,17 L19,17 C19,18.1045695 18.1045695,19 17,19 L3,19 L3,19 C1.8954305,19 1,18.1045695 1,17 L1,3 L1,3 C1,1.8954305 1.8954305,1 3,1 Z"></path>
                    <polyline points="4 11 8 15 16 6"></polyline>
                </svg>
            </div>

            <span>{{ trans('common.other') }}</span>
        </label>
    </div>

    <textarea class="form-control formular_panel_textarea rounded-0 probably_required_textarea"
              id="textarea_{{ $_question->id }}"
              name="{{ $_question->id }}[answer][{{ $_question->attributes['show_textarea'] }}]"
              rows="5"
              style="display: {{ $other ? '' : 'none'}}">{{ $other }}</textarea>

</div>

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#show_textarea_{{ $_question->id }}').change(function () {
                let is_checked = $(this).is(':checked');
                if (!is_checked) {
                    $('#textarea_{{ $_question->id }}').val('');
                    $('#textarea_{{ $_question->id }}').prop('required', false);
                } else {
                    $('#textarea_{{ $_question->id }}').prop('required', true);
                }
                $('#textarea_{{ $_question->id }}').toggle(is_checked);
            });
        });
    </script>
@append

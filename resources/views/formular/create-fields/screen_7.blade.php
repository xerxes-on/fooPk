<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <div class="form-group health-group">
        {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

        @foreach($allergyTypes->where('name', $_question->key_code)->first()->allergies as $value)
            <div class="checkbox">
                <label class="formular_panel_label {{ $value->id }}">
                    <input name="{{ $_question->id }}[answer][{{ $value->slug }}]"
                           type="checkbox"
                           value="{{ $value->name }}"
                    />
                    <span class="checkbox-desc">{{ $value->name }}</span>
                </label>
            </div>
        @endforeach

        <div class="checkbox">
            <label class="formular_panel_label">
                <input id="show_textarea_{{ $_question->id }}"
                       name="{{ $_question->id }}[answer][{{ $_question->attributes['show_textarea'] }}]"
                       type="checkbox"
                />
                {{ trans('common.other') }}
            </label>
        </div>

        <textarea class="form-control formular_panel_textarea rounded-0 probably_required_textarea"
                  id="textarea_{{ $_question->id }}"
                  name="{{ $_question->id }}[answer][{{ $_question->attributes['show_textarea'] }}]"
                  placeholder="{{ trans('survey_questions.any_comments_placeholder') }}"
                  rows="5"
                  style="display: none;">
        </textarea>

    </div>

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
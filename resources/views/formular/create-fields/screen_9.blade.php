<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <div class="form-group {{ $_question->required ? ' required' : '' }}">
        <label class="control-label formular_panel_category">{{ trans('survey_questions.' . $_question->key_code) }}</label>
        {{-- <p>{{ trans('survey_questions.screen_9_description') }}</p> --}}
        <div class="form-group health-group">
            @foreach($_question->options as $key => $value)
                <div class="checkbox" style="margin-right: 5px !important">
                    <label class="formular_panel_label">
                        <input type="checkbox" class="anycomments" id="{{ $key }}"/>
                        {{ trans("survey_questions.$value") }}
                    </label>
                </div>
            @endforeach
        </div>

        <div class="form-group{{ $_question->required ? ' required' : '' }} formulare-textarea">
            <textarea class="form-control formular_panel_textarea rounded-0 probably_required_textarea"
                      id="textarea_{{ $_question->id }}"
                      name="{{ $_question->id }}[answer]"
                      placeholder="{{ trans('survey_questions.any_comments_placeholder') }}"
                      rows="5"
                      style="display: none;">
            </textarea>
        </div>
    </div>

</div>

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('.anycomments').on('change', function () {
                //TODO:: refactor it with proper selection yes/no, because right now it's working not properly
                let is_checked = $('#yes').is(':checked');
                if (!is_checked) {
                    $('#textarea_{{ $_question->id }}').val('');
                    $('#textarea_{{ $_question->id }}').prop('required', false);
                } else {
                    $('#textarea_{{ $_question->id }}').prop('required', true);
                }
                $('#textarea_{{ $_question->id }}').toggle(is_checked);

                if ($('#yes').is(':checked')) {
                    // $('.formular_panel_textarea').toggle(this.checked);
                    $('#no').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');
                    $('#no').prop('disabled', true);
                } else if (!$('#yes').is(':checked')) {
                    $('#textarea_{{ $_question->id }}').val('');
                    $(this).parent().removeClass('checked');
                    $('#no').prop('disabled', false).parent().parent().removeClass('disabled');
                } else if ($('#no').is(':checked')) {
                    $('#yes').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');
                } else {
                    $(this).parent().removeClass('checked');
                    $('#yes').prop('disabled', false).parent().parent().removeClass('disabled');
                }
            });
        });
    </script>
@append

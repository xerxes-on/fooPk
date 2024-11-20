<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <div class="form-group health-group">
        {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

        @foreach($allergyTypes->where('name', $_question->key_code)->first()->allergies as $value)
            <div class="checkbox">
                <label class="formular_panel_label {{ $value }}" for="{{ $value }}">
                    <input name="{{ $_question->id }}[answer][{{ $value->slug }}]"
                           type="checkbox"
                           value="{{ $value->name }}"
                           id="{{ $value }}"
                    />
                    <div class="checkbox-desc">{{ $value->name }}</div>
                </label>
            </div>
        @endforeach

        <div class="checkbox">
            <label class="formular_panel_label">
                <input name="{{ $_question->id }}[answer][{{ $_question->attributes['show_textarea'] }}]"
                       type="checkbox"
                       value="{{ $_question->attributes['show_textarea'] }}"
                />
                {{ trans('common.other') }}
            </label>
        </div>

        <textarea class="form-control formular_panel_textarea rounded-0"
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
            var $name = "{{ $_question->id . '[answer][' . $_question->attributes['show_textarea'] . ']' }}";
            $('input[name="' + $name + '"]').change(function () {
                $('textarea[name="' + $name + '"]').toggle(this.checked);
            });
        });
    </script>
@append
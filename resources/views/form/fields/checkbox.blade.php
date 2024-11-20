<div class="form-group{{ $_question->required ? ' required' : '' }}" data-key={!! ($_question->id) !!}>
    {!! Form::label($_question->id . '[answer]', trans('survey_questions.'.$_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

    @php
        $currentAnswer = ( isset($answer) && key_exists($_question->id, $answer) && !empty($answer[$_question->id]['answer']) ) ? array_keys(json_decode($answer[$_question->id]['answer'], true)) : [];
    @endphp

    @foreach($_question->options as $key => $value)

        @php
            $checkboxStatus = (in_array('vegetarian', $currentAnswer) && ($key === 'paleo' || $key === 'aip' || $key === 'pescetarisch' || $key === 'vegan'))
                || (in_array('vegan', $currentAnswer) && $key === 'vegetarian'|| $key === 'paleo' || $key === 'aip' || $key === 'pescetarisch')
                || (in_array('paleo', $currentAnswer) && $key === 'vegetarian' || $key === 'vegan')
                || (in_array('aip', $currentAnswer) && $key === 'vegetarian'|| $key === 'vegan')
                || (in_array('pescetarisch', $currentAnswer) && $key === 'vegetarian'|| $key === 'vegan')
                || (in_array('ketogenic', $currentAnswer) && ($key === 'low_carb' || $key === 'moderate_carb'))
                || (in_array('low_carb', $currentAnswer) && ($key === 'ketogenic' || $key === 'moderate_carb'))
                || (in_array('moderate_carb', $currentAnswer) && ($key === 'low_carb' || $key === 'ketogenic'))
                || (in_array('no_matter', $currentAnswer) && $key !== 'no_matter')
                ? 'disabled' : '';
        @endphp

        <div class="checkbox cntr {{ $_question->key_code }}">
            <label class="formular_panel_label label-cbx {{ $checkboxStatus }}">

                <input name="{{ $_question->id }}[answer][{{ $key }}]"
                       type="checkbox"
                       value="{{ $value }}"
                       class="particularly-important-anchor invisible"
                       data-key="{{ $key }}"
                       id="{{ $key }}"

                @if(!count($currentAnswer))
                    {{ $_question->required ? ' required' : '' }}
                        @endif

                        {{ in_array($key, $currentAnswer) ? 'checked' : '' }}
                />

                <div class="checkbox-svg">
                    <svg width="20px" height="20px" viewBox="0 0 20 20">
                        <path d="M3,1 L17,1 L17,1 C18.1045695,1 19,1.8954305 19,3 L19,17 L19,17 C19,18.1045695 18.1045695,19 17,19 L3,19 L3,19 C1.8954305,19 1,18.1045695 1,17 L1,3 L1,3 C1,1.8954305 1.8954305,1 3,1 Z"></path>
                        <polyline points="4 11 8 15 16 6"></polyline>
                    </svg>
                </div>

                @if($key == 'no_matter')
                    <span>{{ trans("survey_questions.$_question->key_code".'_'.$key) }}</span>
                @else
                    <span>{{ trans('survey_questions.'.$key) }}</span>
                @endif

            </label>
        </div>

        @if(is_array($_question->attributes) && key_exists('show_textarea', $_question->attributes) && $key === $_question->attributes['show_textarea'])
            <textarea class="form-control formular_panel_textarea rounded-0"
                      name="{{ $_question->id }}[answer][{{ $key }}]"
                      rows="10" style="display: none"></textarea>
        @endif

    @endforeach
</div>

@if($_question->required)
    @section('scripts')
        <script type="text/javascript">
            $(document).ready(function () {
                let requiredCheckboxes = $('.form-group.required .checkbox.{{ $_question->key_code }} :checkbox');
                requiredCheckboxes.change(function () {
                    if (requiredCheckboxes.is(':checked')) {
                        requiredCheckboxes.removeAttr('required');
                        $('#particularly_important-error').hide();
                    } else {
                        requiredCheckboxes.attr('required', 'required');
                    }
                });
            });
        </script>
    @append
@endif

@if($_question->key_code === 'particularly_important')
    @section('scripts')
        <script type="text/javascript">
            $(document).ready(function () {
                $('.particularly-important-anchor').on('change', function () {
                    let current = $(this);

                    if ($('#no_matter').is(':checked') && current.attr('data-key') !== 'no_matter') {
                        $('#no_matter').prop('checked', false);
                        $('.particularly-important-anchor').each(function () {
                            $(this).parent().removeClass('disabled');
                        });
                    }

                    switch (current.attr('data-key')) {
                        case 'no_matter': // egal
                            if (current.is(':checked')) {
                                $('.particularly-important-anchor').each(function () {
                                    if ($(this).attr('data-key') !== 'no_matter') {
                                        $(this).prop('checked', false).parent().addClass('disabled');
                                    }
                                });
                            } else {
                                $('.particularly-important-anchor').each(function () {
                                    $(this).parent().removeClass('disabled');
                                });
                            }
                            break;

                        case 'vegetarian':
                            if (current.is(':checked')) {
                                $(this).parent().removeClass('disabled');
                                $('#vegan').prop('checked', false).parent().addClass('disabled');
                                $('#paleo').prop('checked', false).parent().addClass('disabled');
                                $('#aip').prop('checked', false).parent().addClass('disabled');
                                $('#pescetarisch').prop('checked', false).parent().addClass('disabled');
                                $('input[name="16[answer][milk_protein_all]"]').attr('disabled', true).prop('checked', false).parent().addClass('disabled');
                            } else {
                                $('#vegan').parent().removeClass('disabled');
                                $('#paleo').parent().removeClass('disabled');
                                $('#aip').parent().removeClass('disabled');
                                $('#pescetarisch').parent().removeClass('disabled');
                                $('input[name="16[answer][milk_protein_all]"]').attr('disabled', false).parent().removeClass('disabled');
                            }
                            break;

                        case 'vegan':
                            if (current.is(':checked')) {
                                $(this).parent().removeClass('disabled');
                                $('#vegetarian').prop('checked', false).parent().addClass('disabled');
                                $('#paleo').prop('checked', false).parent().addClass('disabled');
                                $('#aip').prop('checked', false).parent().addClass('disabled');
                                $('#pescetarisch').prop('checked', false).parent().addClass('disabled');
                                $('input[name="16[answer][milk_protein_all]"]').attr('disabled', true).prop('checked', false).parent().addClass('disabled');
                                $('input[name="16[answer][soy]"]').attr('disabled', true).prop('checked', false).parent().addClass('disabled');
                            } else {
                                $('#vegetarian').parent().removeClass('disabled');
                                $('#paleo').parent().removeClass('disabled');
                                $('#aip').parent().removeClass('disabled');
                                $('#pescetarisch').parent().removeClass('disabled');
                                $('input[name="16[answer][milk_protein_all]"]').attr('disabled', false).parent().removeClass('disabled');
                                $('input[name="16[answer][soy]"]').attr('disabled', false).parent().removeClass('disabled');
                            }
                            break;

                        case 'paleo':
                            if (current.is(':checked') || $('#aip').is(':checked') || $('#pescetarisch').is(':checked')) {
                                $(this).parent().removeClass('disabled');

                                if (!$('#aip').is(':checked'))
                                    $('#aip').parent().removeClass('disabled');

                                if (!$('#pescetarisch').is(':checked'))
                                    $('#pescetarisch').parent().removeClass('disabled');

                                $('#vegetarian').prop('checked', false).parent().addClass('disabled');
                                $('#vegan').prop('checked', false).parent().addClass('disabled');
                            } else {
                                $('#vegetarian').parent().removeClass('disabled');
                                $('#vegan').parent().removeClass('disabled');
                            }
                            break;

                        case 'aip':
                            if (current.is(':checked') || $('#paleo').is(':checked') || $('#pescetarisch').is(':checked')) {
                                $(this).parent().removeClass('disabled');

                                if (!$('#paleo').is(':checked'))
                                    $('#paleo').parent().removeClass('disabled');

                                if (!$('#pescetarisch').is(':checked'))
                                    $('#pescetarisch').parent().removeClass('disabled');

                                $('#vegetarian').prop('checked', false).parent().addClass('disabled');
                                $('#vegan').prop('checked', false).parent().addClass('disabled');
                            } else {
                                $('#vegetarian').parent().removeClass('disabled');
                                $('#vegan').parent().removeClass('disabled');
                            }
                            break;

                        case 'pescetarisch':
                            if (current.is(':checked') || $('#paleo').is(':checked') || $('#aip').is(':checked')) {
                                $(this).parent().removeClass('disabled');

                                if (!$('#aip').is(':checked'))
                                    $('#aip').parent().removeClass('disabled');

                                if (!$('#paleo').is(':checked'))
                                    $('#paleo').parent().removeClass('disabled');

                                $('#vegetarian').prop('checked', false).parent().addClass('disabled');
                                $('#vegan').prop('checked', false).parent().addClass('disabled');
                            } else {
                                $('#vegetarian').parent().removeClass('disabled');
                                $('#vegan').parent().removeClass('disabled');
                            }
                            break;

                        case 'ketogenic':
                            if (current.is(':checked')) {
                                $(this).parent().removeClass('disabled');
                                $('#low_carb').prop('checked', false).parent().addClass('disabled');
                                $('#moderate_carb').prop('checked', false).parent().addClass('disabled');
                            } else {
                                $('#low_carb').parent().removeClass('disabled');
                                $('#moderate_carb').parent().removeClass('disabled');
                            }
                            break;

                        case 'low_carb':
                            if (current.is(':checked')) {
                                $(this).parent().removeClass('disabled');
                                $('#ketogenic').prop('checked', false).parent().addClass('disabled');
                                $('#moderate_carb').prop('checked', false).parent().addClass('disabled');
                            } else {
                                $('#ketogenic').parent().removeClass('disabled');
                                $('#moderate_carb').parent().removeClass('disabled');
                            }
                            break;

                        case 'moderate_carb':
                            if (current.is(':checked')) {
                                $(this).parent().removeClass('disabled');
                                $('#ketogenic').prop('checked', false).parent().addClass('disabled');
                                $('#low_carb').prop('checked', false).parent().addClass('disabled');
                            } else {
                                $('#ketogenic').parent().removeClass('disabled');
                                $('#low_carb').parent().removeClass('disabled');
                            }
                            break;
                    }

                });

            });
        </script>
    @append
@endif
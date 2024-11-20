<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <div class="form-group form-radio-main-target kohlenhydratarm health-group form-group{{ $_question->required ? ' required' : '' }}">
        {!! Form::label($_question->id .'[answer]', trans('survey_questions.'. $_question->key_code), ['class' => 'control-label formular_panel_category']) !!}

        @foreach($_question->options as $key => $value)
            <div class="checkbox {{ $_question->key_code }}">

                @if($key != 'no_matter')
                    <div class="range_sports_info">?
                        <span>{{ trans("survey_questions.$_question->key_code" .'_'. $key .'_tooltip') }}</span></div>
                @endif

                <label class="formular_panel_label" style="cursor: pointer !important;">
                    <input name="{{ $_question->id }}[answer][{{ $key }}]"
                           type="checkbox"
                           value="{{ $value }}"
                           class="particularly-important-anchor"
                           data-key="{{ $key }}"
                           id="{{ $key }}"
                    />

                    <span class="label-desc">
                        @if($key == 'no_matter')
                            {{ trans("survey_questions.$_question->key_code".'_'.$key) }}
                        @else
                            {{ trans('survey_questions.'.$key) }}
                        @endif
                    </span>

                </label>
            </div>
        @endforeach

        <input type="hidden" id="particularly-important-anchor"
               value="" {{ $_question->required ? ' required' : '' }} />
    </div>

</div>

@section('scripts')

    <script type="text/javascript">
        $(document).ready(function () {
            $('.particularly-important-anchor').on('change', function () {
                let current = $(this),
                    h = $('#particularly-important-anchor');

                if ($('.particularly-important-anchor:checked').length > 0) {
                    current.closest('.form-group').addClass('has-success').removeClass('has-error');
                    h.val(true);
                } else {
                    current.closest('.form-group').addClass('has-error').removeClass('has-success');
                    h.val('');
                }

                if ($('#no_matter').is(':checked') && current.attr('data-key') !== 'no_matter') {
                    $('#no_matter').prop('checked', false).parent().parent().removeClass('checked');
                    $('.particularly-important-anchor').each(function () {
                        $(this).attr('disabled', false).parent().parent().removeClass('disabled');
                    });
                }

                switch (current.attr('data-key')) {
                    case 'no_matter': // egal
                        if (current.is(':checked')) {
                            $('.particularly-important-anchor').each(function () {
                                if ($(this).attr('data-key') !== 'no_matter') {
                                    $(this).prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');
                                }
                            });
                        } else {
                            $('.particularly-important-anchor').each(function () {
                                $(this).attr('disabled', false).parent().parent().removeClass('disabled');
                            });
                        }
                        break;

                    case 'vegetarian':
                        if (current.is(':checked')) {
                            $(this).parent().parent().removeClass('disabled');

                            $('#aip').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#paleo').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#vegan').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#pescetarisch').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#milk_protein_all').attr('disabled', true).parent().parent().addClass('disabled');
                        } else {
                            $(this).parent().removeClass('checked');
                            $('#aip').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#paleo').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#vegan').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#pescetarisch').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#milk_protein_all').attr('disabled', false).parent().parent().removeClass('disabled');
                        }
                        break;

                    case 'vegan':
                        if (current.is(':checked')) {
                            $(this).parent().parent().removeClass('disabled');

                            $('#aip').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#paleo').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#vegetarian').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#pescetarisch').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#milk_protein_all').attr('disabled', true).parent().parent().addClass('disabled');
                            $('#soy').attr('disabled', true).parent().parent().addClass('disabled');
                        } else {
                            $(this).parent().removeClass('checked');
                            $('#aip').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#paleo').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#vegetarian').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#pescetarisch').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#milk_protein_all').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#soy').attr('disabled', false).parent().parent().removeClass('disabled');
                        }
                        break;

                    case 'paleo':
                        if (current.is(':checked') || $('#aip').is(':checked') || $('#pescetarisch').is(':checked')) {
                            $(this).parent().parent().removeClass('disabled');

                            if (!$('#aip').is(':checked'))
                                $('#aip').parent().parent().removeClass('disabled').removeClass('checked');

                            if (!$('#pescetarisch').is(':checked'))
                                $('#pescetarisch').parent().parent().removeClass('disabled').removeClass('checked');

                            $('#vegetarian').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#vegan').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                        } else {
                            $(this).parent().removeClass('checked');
                            $('#vegetarian').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#vegan').attr('disabled', false).parent().parent().removeClass('disabled');
                        }
                        break;

                    case 'aip':
                        if (current.is(':checked') || $('#paleo').is(':checked') || $('#pescetarisch').is(':checked')) {
                            $(this).parent().parent().removeClass('disabled');

                            if (!$('#paleo').is(':checked'))
                                $('#paleo').parent().parent().removeClass('disabled').removeClass('checked');

                            if (!$('#pescetarisch').is(':checked'))
                                $('#pescetarisch').parent().parent().removeClass('disabled').removeClass('checked');

                            $('#vegetarian').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#vegan').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                        } else {
                            $('#vegetarian').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#vegan').attr('disabled', false).parent().parent().removeClass('disabled');
                        }
                        break;

                    case 'pescetarisch':
                        if (current.is(':checked') || $('#paleo').is(':checked') || $('#aip').is(':checked')) {
                            $(this).parent().parent().removeClass('disabled');

                            if (!$('#aip').is(':checked'))
                                $('#aip').parent().parent().removeClass('disabled').removeClass('checked');

                            if (!$('#paleo').is(':checked'))
                                $('#paleo').parent().parent().removeClass('disabled').removeClass('checked');

                            $('#vegetarian').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#vegan').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                        } else {
                            $('#vegetarian').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#vegan').attr('disabled', false).parent().parent().removeClass('disabled');
                        }
                        break;

                    case 'ketogenic':
                        if (current.is(':checked')) {
                            $(this).parent().parent().removeClass('disabled');

                            $('#low_carb').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#moderate_carb').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');
                        } else {
                            $('#low_carb').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#moderate_carb').attr('disabled', false).parent().parent().removeClass('disabled');
                        }
                        break;

                    case 'low_carb':
                        if (current.is(':checked')) {
                            $(this).parent().parent().removeClass('disabled');

                            $('#ketogenic').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#moderate_carb').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');
                        } else {
                            $('#ketogenic').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#moderate_carb').attr('disabled', false).parent().parent().removeClass('disabled');
                        }
                        break;

                    case 'moderate_carb':
                        if (current.is(':checked')) {
                            $(this).parent().parent().removeClass('disabled');

                            $('#ketogenic').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');

                            $('#low_carb').prop('checked', false).parent().parent().addClass('disabled').removeClass('checked');
                        } else {
                            $('#ketogenic').attr('disabled', false).parent().parent().removeClass('disabled');
                            $('#low_carb').attr('disabled', false).parent().parent().removeClass('disabled');
                        }
                        break;
                }

            });

        });
    </script>
@append
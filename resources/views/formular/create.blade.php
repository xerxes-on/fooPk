@extends('layouts.app')

@section('title', trans('common.formular.title'))

@section('styles')
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker3.min.css"
          rel="stylesheet">
    <link href="//cdn.jsdelivr.net/npm/smartwizard@4.3.1/dist/css/smart_wizard.min.css" rel="stylesheet">
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.css') }}" rel="stylesheet">
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.fp.css') }}" rel="stylesheet">

    <style>
        [data-key="6"],
        [data-key="8"] {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .pre-loader {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transform: -webkit-translate(-50%, -50%);
            transform: -moz-translate(-50%, -50%);
            transform: -ms-translate(-50%, -50%);
        }

        .sk-fading-circle {
            margin: 0 auto 100px;
            width: 40px;
            height: 40px;
            position: relative;
        }

        .sk-fading-circle .sk-circle {
            width: 100%;
            height: 100%;
            position: absolute;
            left: 0;
            top: 0;
        }

        .sk-fading-circle .sk-circle:before {
            content: '';
            display: block;
            margin: 0 auto;
            width: 15%;
            height: 15%;
            background-color: #999;
            border-radius: 100%;
            -webkit-animation: sk-circleFadeDelay 1.2s infinite ease-in-out both;
            animation: sk-circleFadeDelay 1.2s infinite ease-in-out both;
        }

        .sk-fading-circle .sk-circle2 {
            -webkit-transform: rotate(30deg);
            -ms-transform: rotate(30deg);
            transform: rotate(30deg);
        }

        .sk-fading-circle .sk-circle3 {
            -webkit-transform: rotate(60deg);
            -ms-transform: rotate(60deg);
            transform: rotate(60deg);
        }

        .sk-fading-circle .sk-circle4 {
            -webkit-transform: rotate(90deg);
            -ms-transform: rotate(90deg);
            transform: rotate(90deg);
        }

        .sk-fading-circle .sk-circle5 {
            -webkit-transform: rotate(120deg);
            -ms-transform: rotate(120deg);
            transform: rotate(120deg);
        }

        .sk-fading-circle .sk-circle6 {
            -webkit-transform: rotate(150deg);
            -ms-transform: rotate(150deg);
            transform: rotate(150deg);
        }

        .sk-fading-circle .sk-circle7 {
            -webkit-transform: rotate(180deg);
            -ms-transform: rotate(180deg);
            transform: rotate(180deg);
        }

        .sk-fading-circle .sk-circle8 {
            -webkit-transform: rotate(210deg);
            -ms-transform: rotate(210deg);
            transform: rotate(210deg);
        }

        .sk-fading-circle .sk-circle9 {
            -webkit-transform: rotate(240deg);
            -ms-transform: rotate(240deg);
            transform: rotate(240deg);
        }

        .sk-fading-circle .sk-circle10 {
            -webkit-transform: rotate(270deg);
            -ms-transform: rotate(270deg);
            transform: rotate(270deg);
        }

        .sk-fading-circle .sk-circle11 {
            -webkit-transform: rotate(300deg);
            -ms-transform: rotate(300deg);
            transform: rotate(300deg);
        }

        .sk-fading-circle .sk-circle12 {
            -webkit-transform: rotate(330deg);
            -ms-transform: rotate(330deg);
            transform: rotate(330deg);
        }

        .sk-fading-circle .sk-circle2:before {
            -webkit-animation-delay: -1.1s;
            animation-delay: -1.1s;
        }

        .sk-fading-circle .sk-circle3:before {
            -webkit-animation-delay: -1s;
            animation-delay: -1s;
        }

        .sk-fading-circle .sk-circle4:before {
            -webkit-animation-delay: -0.9s;
            animation-delay: -0.9s;
        }

        .sk-fading-circle .sk-circle5:before {
            -webkit-animation-delay: -0.8s;
            animation-delay: -0.8s;
        }

        .sk-fading-circle .sk-circle6:before {
            -webkit-animation-delay: -0.7s;
            animation-delay: -0.7s;
        }

        .sk-fading-circle .sk-circle7:before {
            -webkit-animation-delay: -0.6s;
            animation-delay: -0.6s;
        }

        .sk-fading-circle .sk-circle8:before {
            -webkit-animation-delay: -0.5s;
            animation-delay: -0.5s;
        }

        .sk-fading-circle .sk-circle9:before {
            -webkit-animation-delay: -0.4s;
            animation-delay: -0.4s;
        }

        .sk-fading-circle .sk-circle10:before {
            -webkit-animation-delay: -0.3s;
            animation-delay: -0.3s;
        }

        .sk-fading-circle .sk-circle11:before {
            -webkit-animation-delay: -0.2s;
            animation-delay: -0.2s;
        }

        .sk-fading-circle .sk-circle12:before {
            -webkit-animation-delay: -0.1s;
            animation-delay: -0.1s;
        }

        @-webkit-keyframes sk-circleFadeDelay {
            0%, 39%, 100% {
                opacity: 0;
            }
            40% {
                opacity: 1;
            }
        }

        @keyframes sk-circleFadeDelay {
            0%, 39%, 100% {
                opacity: 0;
            }
            40% {
                opacity: 1;
            }
        }
    </style>
@endsection

@section('content')

    <div class="container formular">

        <div class="pre-loader">
            <div class="sk-fading-circle">
                <div class="sk-circle1 sk-circle"></div>
                <div class="sk-circle2 sk-circle"></div>
                <div class="sk-circle3 sk-circle"></div>
                <div class="sk-circle4 sk-circle"></div>
                <div class="sk-circle5 sk-circle"></div>
                <div class="sk-circle6 sk-circle"></div>
                <div class="sk-circle7 sk-circle"></div>
                <div class="sk-circle8 sk-circle"></div>
                <div class="sk-circle9 sk-circle"></div>
                <div class="sk-circle10 sk-circle"></div>
                <div class="sk-circle11 sk-circle"></div>
                <div class="sk-circle12 sk-circle"></div>
            </div>
        </div>

        <div class="formular_panel respons" style="display: none">

            @guest
                {!! Form::open(['route' => 'formular.tryForFree.store', 'method' => 'POST', 'files' => true, 'id' => 'formularCreate']) !!}
            @else
                {!! Form::open(['route' => 'formular.store', 'method' => 'POST', 'files' => true, 'id' => 'formularCreate']) !!}
            @endguest

            @if ($errors->any())
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="smartwizard">
                @php
                    $screens = auth()->guest()?  11 : 10 ;
                @endphp
                <ul>
                    @for($i=1;$i <= $screens; $i++)
                        <li><a href="#step-{{$i}}">Step Title<br/><small>Step description</small></a></li>
                    @endfor
                </ul>

                <div>
                    <div id="step-1" class="step-1">
                        @include('formular.create-fields.screen_1', ['_question' => $questions['main_target'], '_dataKey' => $dataKey = 1])
                    </div>
                    <div id="step-2" class="">
                        @include('formular.create-fields.screen_6', ['_question' => $questions['particularly_important'], '_dataKey' => ++$dataKey])
                    </div>
                    <div id="step-3" class="">
                        @include('formular.create-fields.screen_8', ['_question' => $questions['allergy'], '_dataKey' => ++$dataKey])
                    </div>
                    <div id="step-4" class="">
                        @include('formular.create-fields.screen_7', ['_question' => $questions['disease'], '_dataKey' => ++$dataKey])
                    </div>
                    <div id="step-5" class="">
                        @include('formular.create-fields.screen_4', ['_question' => $questions['life_activity'], '_dataKey' => ++$dataKey])
                    </div>
                    <div id="step-6" class="step-6">
                        @include('formular.create-fields.screen_5', ['_qIntensiveSports' => $questions['intensive_sports'], '_qModerateSports' => $questions['moderate_sports'], '_qLightSports' => $questions['light_sports'], '_dataKey' => ++$dataKey])
                    </div>
                    <div id="step-7" class="step-2">
                        @include('formular.create-fields.screen_2', ['_qGender' => $questions['gender'], '_qAge' => $questions['age'], '_dataKey' => ++$dataKey])
                    </div>
                    <div id="step-8" class="step-3">
                        @include('formular.create-fields.screen_3', ['_qWeight' => $questions['weight'], '_qGrowth' => $questions['growth'], '_qFatPercentage' => $questions['fat_percentage'], '_dataKey' => ++$dataKey])
                    </div>
                    <div id="step-9" class="">
                        @include('formular.create-fields.screen_daily_routine', ['_question' => $questions['daily_routine'], '_dataKey' => ++$dataKey])
                    </div>
                    <div id="step-10" class="">
                        @include('formular.create-fields.screen_9', ['_question' => $questions['any_comments'], '_dataKey' => ++$dataKey])
                    </div>
                    {{--                    <div id="step-11" class="">--}}
                    {{--                        @include('formular.create-fields.screen_knowus', ['_dataKey' => ++$dataKey])--}}
                    {{--                    </div>--}}
                    @guest
                        <div id="step-11" class="">
                            @include('formular.create-fields.screen_email', ['_dataKey' => ++$dataKey])
                        </div>
                    @endguest
                </div>

            </div>


            <div class="text-center">
                <div id="progressbar"></div>
                <div class="text-center-wrapper">

                    <div class="left_field">
                        <button class="prev" type="button"></button>
                        <p>
                            {{ trans('survey_questions.step') }} <span class="current_item">1</span> / <span
                                    class="item_quantity">1</span>
                        </p>
                    </div>
                    {!! Form::submit('Submit', ['class' => 'btn btn-tiffany']) !!}
                    <button class="next" type="button">{{ trans('survey_questions.next') }}</button>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>

@endsection

@section('scripts')
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/moment@latest/moment.min.js"></script>
    <script src="{{ mix('vendor/modernizr/modernizr.custom.80028.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/smartwizard@4.3.1/dist/js/jquery.smartWizard.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.de.min.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/localization/messages_de.min.js"></script>

    <script src="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.min.js') }}"></script>

    <script type="text/javascript">
        let $screens = $('#formularCreate .screen-wrapper'),
            count = $screens.length,
            $form = $('#formularCreate');

        $(document).ready(function () {

            // Custom method to validate username
            $.validator.addMethod('isValidEmailAddress', function (value, element) {
                let pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
                return this.optional(element) || pattern.test(value);
            }, "{{ trans('common.please_enter_valid_email') }}");

            $.validator.addMethod('validDate', function (value, element) {
                return this.optional(element) || moment(value, 'DD.MM.YYYY', true).isValid();
            }, "{{ trans('survey_questions.date_format') }}");

            let validator = $form.validate({
                lang: 'de',
                ignore: [],
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    let wrapper = $(element).parents('.screen-wrapper');

                    error.addClass('formular-help-block');
                    if (wrapper.find('.formular-help-block').length === 0) {
                        $(element).parents('.screen-wrapper').prepend(error);
                    }
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).closest('.form-group').addClass('has-error').removeClass('has-success');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).closest('.form-group').addClass('has-success').removeClass('has-error');
                },
                rules: {
                    email: {
                        required: true,
                        isValidEmailAddress: true,
                        remote: {
                            url: "{{ route('user.check.email.formular') }}",
                            type: 'POST',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                email: function () {
                                    return $('#email').val();
                                },
                            },
                        },
                    },
                },
                messages: {
                    email: {
                        required: "{{ trans('common.email_is_required') }}",
                        email: "{{ trans('common.please_enter_valid_email') }}",
                        remote: "{{ trans('common.email_already_used') }}",
                    },
                },
            });

            // add custom messagr
            $.validator.messages.required = "{{ trans('common.field_is_required') }}";
            $.validator.messages.dateISO = "{{ trans('survey_questions.date_format')}}";
            $.validator.messages.min = "{{ trans('survey_questions.min')}}";
            $.validator.messages.max = "{{ trans('survey_questions.max')}}";

            // init DatePicker for birth
            let dateOfBirth = document.getElementById('date-of-birth');
            dateOfBirth.setAttribute('type', 'dates');

            // if browser doesn't support input type="date", initialize date picker widget:
            if (dateOfBirth.type !== 'date') {
                dateOfBirth.setAttribute('type', 'text'); // IE fix
                $(function ($) {
                    $('#date-of-birth').datepicker({
                        daysOfWeekHighlighted: '0,6',
                        todayHighlight: true,
                        weekStart: 1,
                        format: 'dd.mm.yyyy',
                        language: "{{ app()->getLocale() }}",
                        startDate: '-100y',
                        endDate: '-16y',
                    }).on('change', function () {
                        let selectedDate = new Date($(this).val());
                        checkAge(selectedDate, true);
                    }).rules('add', {required: true, validDate: true});
                });
            } else {
                $('#date-of-birth').on('change', function () {
                    let selectedDate = new Date($(this).val());
                    checkAge(selectedDate, true);
                }).rules('add', {required: true, validDate: true});
            }

            // add custom field rules digit
            $('input[data-rule-digits="true"]').each(function () {
                $(this).rules('add', {
                    required: true,
                    digits: true,
                });
            });

            // add custom field rules number
            $('input[data-rule-number="true"]').each(function () {
                $(this).rules('add', {
                    required: true,
                    number: true,
                });
            });

            // init progressbar
            initProgressBar();

            // fix ancho url scrollTop
            $('html,body').stop().animate({
                scrollTop: $('#smartwizard').offset().top - 125, //offsets for fixed header
            }, 'linear');

            $('#smartwizard').smartWizard({
                autoAdjustHeight: false,
                keyNavigation: false,
                //useURLhash: false,
                //showStepURLhash: false,
                toolbarSettings: {
                    toolbarPosition: 'none',
                },
                anchorSettings: {
                    anchorClickable: false,
                },
                transitionEffect: 'fade',
                transitionSpeed: '1000',
            });

            setTimeout(function () {
                $('#smartwizard').smartWizard('reset');
                $('.pre-loader').fadeOut();
                $('.formular_panel').fadeIn(1000);
            }, 2000);

            // next slide
            $('.text-center .next').on('click', function () {
                // TODO:: refactor it!!
                // code for know us question
                if ($('#step-11').is(':visible')) {
                    {{--    if ($(".know_us").hasClass('checked')) {--}}
                    {{--        if ($("#partner").hasClass('checked')) {--}}
                    {{--            if (!$('#foodpunk-partner').val()) {--}}
                    {{--                alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--                $('#smartwizard').smartWizard("");--}}
                    {{--            } else {--}}
                    {{--                $('#smartwizard').smartWizard("next");--}}
                    {{--                formSave();--}}
                    {{--            }--}}
                    {{--        }--}}
                    {{--        if ($("#empfehlung").hasClass('checked')) {--}}
                    {{--            if (!$('#empfehlung-freundenr').val()) {--}}
                    {{--                alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--                $('#smartwizard').smartWizard("");--}}
                    {{--            } else {--}}
                    {{--                $('#smartwizard').smartWizard("next");--}}
                    {{--                formSave();--}}
                    {{--            }--}}
                    {{--        }--}}
                    {{--        if ($("#blog").hasClass('checked')) {--}}
                    {{--            if (!$('#foodpunk_blog').val()) {--}}
                    {{--                alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--                $('#smartwizard').smartWizard("");--}}
                    {{--            } else {--}}
                    {{--                $('#smartwizard').smartWizard("next");--}}
                    {{--                formSave();--}}
                    {{--            }--}}
                    {{--        }--}}
                    {{--        if ($("#others").hasClass('checked')) {--}}
                    {{--            if (!$('#sonstige').val()) {--}}
                    {{--                alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--                $('#smartwizard').smartWizard("");--}}
                    {{--            } else {--}}
                    {{--                $('#smartwizard').smartWizard("next");--}}
                    {{--                formSave();--}}
                    {{--            }--}}
                    {{--        }--}}
                    {{--        if ($("#google_suche").hasClass('checked')) {--}}
                    {{--            $('#smartwizard').smartWizard("next");--}}
                    {{--            formSave();--}}
                    {{--        }--}}
                    {{--        if ($("#social").hasClass('checked')) {--}}
                    {{--            if ($(".youtube").hasClass('checked')) {--}}
                    {{--                $('#smartwizard').smartWizard("next");--}}
                    {{--                formSave();--}}
                    {{--            } else if ($(".instagram").hasClass('checked')) {--}}
                    {{--                $('#smartwizard').smartWizard("next");--}}
                    {{--                formSave();--}}
                    {{--            } else if ($(".facebook").hasClass('checked')) {--}}
                    {{--                $('#smartwizard').smartWizard("next");--}}
                    {{--                formSave();--}}
                    {{--            } else {--}}
                    {{--                alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--                $('#smartwizard').smartWizard("");--}}
                    {{--            }--}}
                    {{--        }--}}
                    {{--    } else {--}}
                    {{--        alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--        $('#smartwizard').smartWizard("");--}}
                    {{--    }--}}
                }
                // validation for allergies
                else if ($('#step-3').is(':visible')) {
                    let allowGoToNextStep = true;
                    let formElementSelector = '#step-3 .probably_required_textarea';
                    if ($(formElementSelector).length) {
                        let formElement = $(formElementSelector).first();
                        let hasRequired = formElement.prop('required');
                        let val = formElement.val();
                        if (hasRequired && val == '') {
                            allowGoToNextStep = false;
                        }
                    }
                    if (allowGoToNextStep) {
                        $('#smartwizard').smartWizard('next');
                        formSave();
                    }
                }
                // validation for diseases
                else if ($('#step-4').is(':visible')) {
                    let allowGoToNextStep = true;
                    let formElementSelector = '#step-4 .probably_required_textarea';
                    if ($(formElementSelector).length) {
                        let formElement = $(formElementSelector).first();
                        let hasRequired = formElement.prop('required');
                        let val = formElement.val();
                        if (hasRequired && val == '') {
                            allowGoToNextStep = false;
                        }
                    }
                    if (allowGoToNextStep) {
                        $('#smartwizard').smartWizard('next');
                        formSave();
                    }
                }
                // validation for any comments
                else if ($('#step-10').is(':visible')) {
                    let allowGoToNextStep = true;
                    let formElementSelector = '#step-10 .probably_required_textarea';
                    if ($(formElementSelector).length) {
                        let formElement = $(formElementSelector).first();
                        let hasRequired = formElement.prop('required');
                        let val = formElement.val();
                        if (hasRequired && val == '') {
                            allowGoToNextStep = false;
                        }
                    }
                    if (allowGoToNextStep) {
                        $('#smartwizard').smartWizard('next');
                        formSave();
                    }
                } else {
                    $('#smartwizard').smartWizard('next');
                    formSave();
                }
                /*$('html,body').stop().animate({
                    scrollTop: $('#smartwizard').offset().top - 125 //offsets for fixed header
                }, 'linear');*/
            });

            // prev slide
            $('.text-center .prev').click(function () {
                $('#smartwizard').smartWizard('prev');
            });

            $('#smartwizard').on('showStep', function (e, anchorObject, stepNumber, stepDirection) {
                setProgressBar(stepNumber + 1);
            });

            $('#smartwizard').on('leaveStep', function (e, anchorObject, stepNumber, stepDirection) {
                let screenValid = true;

                $($screens[stepNumber]).find('.formular-help-block').remove();

                // stepDirection === 'forward' :- this condition allows to do the form validation
                // only on forward navigation, that makes easy navigation on backwards still do the validation when going next
                if (stepDirection === 'forward') {

                    $($screens[stepNumber]).find('.form-group').each(function (index, value) {
                        let input = $(this).find('input').first();

                        if ($(this).hasClass('health-group')) {
                            input = $(this).find('input#particularly-important-anchor');
                        }

                        if ((input.prop('required') === true) && (validator.element(input) === false)) {
                            screenValid = false;
                        }

                    });
                }

                return screenValid;
            });

            // click on input
            $('.form-radio-main-target .radio').click(function () {
                $(this).parent().removeClass('checkedParent').find('.radio').removeClass('checked');
                $(this).addClass('checked');
            });

            $('.health-group .checkbox label').on('change', function () {
                $(this).parent().toggleClass('checked');
            });

            // TODO:: REFACTOR TO VUE????
            // THIS IS SUBMIT BUTTON FOR WHOLE FORM
            $('.formular_panel .text-center .btn-tiffany').on('click', function (e) {
                let validLastStep = true;
                e.preventDefault();
                e.stopPropagation();

                $form.find('.formular-help-block').remove();
                sessionStorage.removeItem('formularCreate');

                @guest
                if ((validator.element($('#email')) === false) || (validator.element($('#register-agree')) === false)) {
                    validLastStep = false;
                    validator.form();
                }

                if (validLastStep && !$form.find('.form-group-hidden').is(':visible')) {
                    $form.find('.form-group-hidden').show();
                } else if (validLastStep && $form.find('.form-group-hidden').is(':visible')) {
                    validator.destroy();
                    $form.submit();
                }

                @else
                if ($('#step-11').is(':visible')) {
                    {{--if ($(".know_us").hasClass('checked')) {--}}
                    {{--    if ($("#partner").hasClass('checked')) {--}}
                    {{--        if (!$('#foodpunk-partner').val()) {--}}
                    {{--            alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--            $('#smartwizard').smartWizard("");--}}
                    {{--        } else {--}}
                    {{--            validator.destroy();--}}
                    {{--            $form.submit();--}}
                    {{--        }--}}
                    {{--    }--}}
                    {{--    if ($("#empfehlung").hasClass('checked')) {--}}
                    {{--        if (!$('#empfehlung-freundenr').val()) {--}}
                    {{--            alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--            $('#smartwizard').smartWizard("");--}}
                    {{--        } else {--}}
                    {{--            validator.destroy();--}}
                    {{--            $form.submit();--}}
                    {{--        }--}}
                    {{--    }--}}
                    {{--    if ($("#blog").hasClass('checked')) {--}}
                    {{--        if (!$('#foodpunk_blog').val()) {--}}
                    {{--            alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--            $('#smartwizard').smartWizard("");--}}
                    {{--        } else {--}}
                    {{--            validator.destroy();--}}
                    {{--            $form.submit();--}}
                    {{--        }--}}
                    {{--    }--}}
                    {{--    if ($("#others").hasClass('checked')) {--}}
                    {{--        if (!$('#sonstige').val()) {--}}
                    {{--            alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--            $('#smartwizard').smartWizard("");--}}
                    {{--        } else {--}}
                    {{--            validator.destroy();--}}
                    {{--            $form.submit();--}}
                    {{--        }--}}
                    {{--    }--}}
                    {{--    if ($("#google_suche").hasClass('checked')) {--}}
                    {{--        validator.destroy();--}}
                    {{--        $form.submit();--}}
                    {{--    }--}}
                    {{--    if ($("#social").hasClass('checked')) {--}}
                    {{--        if ($(".youtube").hasClass('checked')) {--}}
                    {{--            validator.destroy();--}}
                    {{--            $form.submit();--}}
                    {{--        } else if ($(".instagram").hasClass('checked')) {--}}
                    {{--            validator.destroy();--}}
                    {{--            $form.submit();--}}
                    {{--        } else if ($(".facebook").hasClass('checked')) {--}}
                    {{--            validator.destroy();--}}
                    {{--            $form.submit();--}}
                    {{--        } else {--}}
                    {{--            alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--            $('#smartwizard').smartWizard("");--}}
                    {{--        }--}}
                    {{--    }--}}
                    {{--} else {--}}
                    {{--    alert("{{ trans('survey_questions.must_answer') }}");--}}
                    {{--    $('#smartwizard').smartWizard("");--}}
                    {{--}--}}
                } else {
                    validator.destroy();
                    $form.submit();
                }
                @endguest
            });

            restoreFormData();
        });

        function initProgressBar() {
            let currentStep = location.hash ? location.hash.replace('#step-', '') : 1;

            $('.item_quantity').text(count);
            for (var i = 0; i < count; i++) {
                $('#progressbar').append('<div></div>');
            }
            setProgressBar(currentStep);
        }

        function setProgressBar(stepX) {
            let lastStep = $('#smartwizard > ul li').length;

            $('#progressbar div').removeClass('active');

            $('#progressbar div').each(function (index) {
                if (index == stepX) return false;
                $(this).addClass('active');
            });
            $('.current_item').text(stepX);

            if (stepX == lastStep) {
                $('.text-center .next').hide();
                $('.formular_panel .text-center .btn-tiffany').show();
            } else {
                $('.text-center .next').show();
                $('.formular_panel .text-center .btn-tiffany').hide();
            }
        }

        function formSave() {
            let formData = $('form#formularCreate').serializeArray(),
                values = {};

            $.each(formData, function (key, item) {
                if (item.name != '_token' && item.value != '' && item.value != 0) {
                    values[item.name] = item.value;
                }
            });

            //lastly we set our values for current form in storage as JSON string. Parsed as JSON on page load.
            sessionStorage.setItem(encodeURI('formularCreate'), JSON.stringify(values));
        }

        function restoreFormData() {
            let answerOthers = ['15[answer][others]', '16[answer][sonstiges]'];

            if (sessionStorage.getItem('formularCreate')) {

                //then parse as JSON
                let values = JSON.parse(sessionStorage.getItem('formularCreate'));

                //loop through storage values and populate our forms
                $.each(values, function (key, value) {

                    //give var value to the current field to use during loop
                    let currentField = $('form#formularCreate [name=\'' + key + '\']');

                    //if this current field is a radio or check box
                    if (currentField.is(':radio') || currentField.is(':checkbox')) {

                        /*if its a checkbox checked has to be set via attr
                        instead of prop for some reason...tried setting them both
                        but since radio and checbox are different in JQuery's eyes
                        we must run this uncovenient code to check what kind and
                        set it appropriately*/
                        if (currentField.is(':checkbox')) {
                            currentField.attr('checked', true).change();

                            if (answerOthers.indexOf(key) !== -1) {
                                $(currentField[1]).val(value);
                            }
                        }
                        //else its a radio so use prop to set checked attribute
                        else {
                            // and since radio buttons names can be duplicate we should select by value
                            $('form#formularCreate [value=\'' + value + '\']').prop('checked', true).parent().toggleClass('checked');
                        }
                    }

                    //else its something other than radio/checkbox
                    else {
                        currentField.val(value);
                    }

                });

            }
        }

        function checkAge(selectedDate, dateType) {
            let currentDate = new Date();
            let age = currentDate.getFullYear() - selectedDate.getFullYear();
            let m = currentDate.getMonth() - selectedDate.getMonth();

            if (m < 0 || (m === 0 && currentDate.getDate() < selectedDate.getDate())) age--;

            if (age < 16) {
                if (dateType) {
                    $('#date-of-birth').val('');
                } else {
                    $('#date-of-birth').datepicker('update', '');
                }
                alert("{{ trans('survey_questions.under_16') }}");
            }
        }

    </script>
@append

{{--TODO: deprecated question. remove--}}
<div class="form-group required"
     @if(!is_null(\Auth::user()) && \Auth::user()->role !== 1 && \Auth::user()->isFormularExist()) style="display: none;" @endif >
    <div class="form-group know_us" style="margin: 20px 0px -10px 0px;">
        <label class="control-label formular_panel_category">{{ trans('survey_questions.know_us') }}</label>
    </div>

    <div class="form-group-radio activity form-radio-main-target" style="margin-bottom: 0px;">
        <div class="radio know_us" id="partner">
            <label for="foodpunk-partner" class="formular_panel_label">{{ trans('survey_questions.option1') }}</label>
        </div>
        <div id="foodpunkpartner" class="form-group-radio activity know_us form-radio-main-target"
             style="display: none; margin-bottom: 0px;">
            <div class="radio know_us">
                <input name="know_us[]" id="foodpunk-partner" type="text"
                       style="background-color: #faf5f8; margin: 0px 100px 0px 0px;"
                       class="formular_panel_label answer-require"
                       placeholder="Name des Partners">
            </div>
        </div>
        <div class="radio know_us" id="empfehlung">
            <label for="empfehlung-freundenr"
                   class="formular_panel_label">{{ trans('survey_questions.option2') }}</label>
        </div>
        <div id="empfehlungfreunden" class="form-group-radio know_us activity form-radio-main-target"
             style="display: none; margin-bottom: 0px;">
            <div class="radio">
                <input name="know_us[]" id="empfehlung-freundenr" type="text"
                       style="background-color: #faf5f8; margin: 0px 100px 0px 0px; "
                       class="formular_panel_label answer-require"
                       placeholder="Name o. E-Mail-Adresse des Freundes">
            </div>
        </div>
        <div class="radio social know_us" id="social">
            <label class="formular_panel_label">{{ trans('survey_questions.option3') }}</label>
        </div>
        <div id="social_media" class="form-group-radio know_us activity form-radio-main-target"
             style="display: none; margin-bottom: 0px;">
            <div class="form-group radio know_us youtube">
                <label for="youtube" class="formular_panel_label">{{ trans('survey_questions.option31') }}</label>
                <input name="know_us[]" class="form-control answer-require" type="radio" value="youtube" id="youtube">
            </div>
            <div class="form-group radio know_us instagram">
                <label for="instagram" class="formular_panel_label">{{ trans('survey_questions.option32') }}</label>
                <input name="know_us[]" class="form-control answer-require" type="radio" value="instagram"
                       id="instagram">
            </div>
            <div class="form-group radio know_us facebook">
                <label for="facebook" class="formular_panel_label">{{ trans('survey_questions.option33') }}</label>
                <input name="know_us[]" class="form-control answer-require" type="radio" value="facebook" id="facebook">
            </div>
        </div>
        <div class="form-group radio know_us" id="google_suche">
            <label for="google" class="formular_panel_label">{{ trans('survey_questions.option4') }}</label>
            <input name="know_us[]" class="form-control answer-require" type="radio" id="google" value="google-suche">
        </div>
        <div class="radio know_us" id="blog">
            <label for="foodpunk_blog" class="formular_panel_label">{{ trans('survey_questions.option5') }}</label>
            <input type="radio">
        </div>
        <div id="foodpunkblog" class="form-group-radio know_us activity form-radio-main-target"
             style="display: none; margin-bottom: 0px;">
            <div class="radio">
                <input name="know_us[]" id="foodpunk_blog" type="text"
                       style="background-color: #faf5f8; margin: 0px 100px 0px 0px;"
                       class="formular_panel_label answer-require"
                       placeholder="Name des Blogbeitrages">
            </div>
        </div>
        <div class="radio know_us" id="others">
            <label for="sonstige" class="formular_panel_label">{{ trans('survey_questions.option6') }}</label>
        </div>
        <div id="other" class="form-group-radio activity know_us form-radio-main-target"
             style="display: none; margin-bottom: 0px;">
            <div class="radio">
                <input name="know_us[]" id="sonstige" type="text"
                       style="background-color: #faf5f8; margin: 0px 100px 0px 0px;"
                       class="formular_panel_label answer-require" placeholder="sonstige">
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            // hide show form fields on click
            $('.radio').click(function () {
                if ($(this).attr('id') == 'social') {
                    $('#social_media').show();
                    $('#foodpunkblog').hide();
                    $('#foodpunkpartner').hide();
                    $('#empfehlungfreunden').hide();
                    $('#other').hide();
                } else if ($(this).attr('id') == 'google_suche') {
                    $('#social_media').hide();
                    $('#foodpunkblog').hide();
                    $('#foodpunkpartner').hide();
                    $('#empfehlungfreunden').hide();
                    $('#other').hide();
                } else if ($(this).attr('id') == 'blog') {
                    $('#foodpunkblog').show();
                    $('#social_media').hide();
                    $('#foodpunkpartner').hide();
                    $('#empfehlungfreunden').hide();
                    $('#other').hide();
                } else if ($(this).attr('id') == 'partner') {
                    $('#foodpunkpartner').show();
                    $('#social_media').hide();
                    $('#foodpunkblog').hide();
                    $('#empfehlungfreunden').hide();
                    $('#other').hide();
                } else if ($(this).attr('id') == 'empfehlung') {
                    $('#empfehlungfreunden').show();
                    $('#social_media').hide();
                    $('#foodpunkblog').hide();
                    $('#foodpunkpartner').hide();
                    $('#other').hide();
                } else if ($(this).attr('id') == 'others') {
                    $('#other').show();
                    $('#social_media').hide();
                    $('#foodpunkblog').hide();
                    $('#foodpunkpartner').hide();
                    $('#empfehlungfreunden').hide();
                }
            });

            $('input[name=\'know_us[]\']').click(function () {
                var is_checked = $(this).is(':checked');
                if (!is_checked) {
                    $('input[name=\'know_us[]\']').val('');
                }
            });
        });
    </script>
@append

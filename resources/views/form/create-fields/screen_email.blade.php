<div class="screen-wrapper" data-key="{{ $_dataKey }}">

    <div class="register-description">Fast am Ziel!</div>
    <div class="register-subdescription">An welche Email-Adresse sollen wir deinen Ernährungsplan senden?</div>

    <div class="form-group required">
        <label for="email" class="control-label auth_panel_label">{{ trans('auth.email_address') }}</label>
        <input id="email" type="email" class="form-control auth_panel_input" name="email" value="{{ old('email') }}"
               placeholder="{{ trans('auth.email_address') }}" required autofocus>
    </div>

    <div class="form-group-hidden" style="display: none;">
        <div class="form-group">
            <label for="first_name" class="control-label auth_panel_label">{{ trans('auth.first_name') }}</label>
            <input id="first_name" type="text" class="form-control auth_panel_input" name="first_name"
                   value="{{ old('first_name') }}" placeholder="{{ trans('auth.first_name') }}" autofocus>
        </div>

        <div class="form-group">
            <label for="last_name" class="control-label auth_panel_label">{{ trans('auth.last_name') }}</label>
            <input id="last_name" type="text" class="form-control auth_panel_input" name="last_name"
                   value="{{ old('last_name') }}" placeholder="{{ trans('auth.last_name') }}" autofocus>
        </div>

        {{--{!! htmlFormSnippet() !!}--}}
    </div>

    <div class="form-group required">
        <input name="agree" type="checkbox" id="register-agree" required
               data-msg-required="Bitte akzeptiere die Datenverarbeitung, damit wir deinen Plan erstellen können.">
        <label class="label" for="register-agree" style="color: #000;">
            <span>Ich bin mit der Speicherung und Verarbeitung meiner Daten einverstanden. </span>
            <a href="https://foodpunk.com/de/datenschutz/ " target="_blank">Datenschutzerklärung</a>
        </label>
    </div>

</div>
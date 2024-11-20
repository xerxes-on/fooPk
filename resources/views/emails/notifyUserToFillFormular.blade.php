<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #f5f8fa; color: #74787e; height: 100%; hyphens: auto; line-height: 1.4; margin: 0; -moz-hyphens: auto; -ms-word-break: break-all; width: 100% !important; -webkit-hyphens: auto; -webkit-text-size-adjust: none; word-break: break-word;">
<style>
    @media only screen and (max-width: 600px) {
        .inner-body {
            width: 100% !important;
        }

        .footer {
            width: 100% !important;
        }
    }

    @media only screen and (max-width: 500px) {
        .button {
            width: 100% !important;
        }
    }
</style>
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0"
       style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #f5f8fa; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
    <tr>
        <td align="center" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
            <table class="content" width="100%" cellpadding="0" cellspacing="0"
                   style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                <!-- Email Header -->
                <tr>
                    <td class="header"
                        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 25px 0; text-align: center;">
                        <a href="{{ url(config('app.url_meinplan')) }}"
                           style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #bbbfc3; font-size: 19px; font-weight: bold; text-decoration: none; text-shadow: 0 1px 0 #ffffff;">
                            @lang('email.formular.missing.subject')
                        </a>
                    </td>
                </tr>
                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0"
                        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; border-bottom: 1px solid #edeff2; border-top: 1px solid #edeff2; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                               style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; margin: 0 auto; padding: 0; width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">

                            <!-- Body content -->
                            <tr>

                                <td class="content-cell"
                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                                    <h1 style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 19px; font-weight: bold; margin-top: 0; text-align: left;">
                                        @lang('email.formular.missing.greeting', ['name' => $userName])
                                    </h1>

                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">@lang('email.formular.missing.line1')</p>

                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">@lang('email.formular.missing.line2')</p>

                                    <table class="action" align="center" width="100%" cellpadding="0" cellspacing="0"
                                           style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 30px auto; padding: 0; text-align: center; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                                        <tr>
                                            <td align="center"
                                                style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                                       style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                    <tr>
                                                        <td align="center"
                                                            style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                            <table border="0" cellpadding="0" cellspacing="0"
                                                                   style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                                <tr>
                                                                    <td style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                                                                        <a href="{{ $actionUrl }}"
                                                                           class="button button-blue"
                                                                           target="_blank"
                                                                           style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; border-radius: 3px; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); color: #ffffff; display: inline-block; text-decoration: none; -webkit-text-size-adjust: none; background-color: #D2A596; border-top: 10px solid #D2A596; border-right: 18px solid #D2A596; border-bottom: 10px solid #D2A596; border-left: 18px solid #D2A596;">
                                                                            @lang('email.formular.missing.reset_password_button')
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        @lang('email.formular.missing.line3', ['amount' => $passwordExpirationDays])
                                    </p>
                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        @lang('email.formular.missing.line4')
                                    </p>
                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        @lang('email.formular.missing.line5')
                                    </p>
                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        @lang('email.formular.missing.line6')
                                    </p>

                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        @lang('email.formular.missing.regards')<br>
                                        @lang('email.formular.missing.team')
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <!-- Email Footer -->
                <tr>
                    <td style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0"
                               style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0 auto; padding: 0; text-align: center; width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">
                            <tr>
                                <td class="content-cell" align="center"
                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; line-height: 1.5em; margin-top: 0; color: #aeaeae; font-size: 12px; text-align: left;">
                                        <b>@lang('email.footer.contact'):</b><br/>
                                        <a href="mailto:info@foodpunk.de">info@foodpunk.de</a><br/>
                                        @lang('email.footer.chat')<br/>
                                        <a href="tel:+498935762016">+49 89 35762016</a> (8-16 h)<br/>
                                        <br/><br/>

                                        <b>@lang('email.footer.address'):</b><br/>
                                        Foodpunk GmbH<br/>
                                        Georg-Knorr-Str. 21<br/>
                                        85662 Hohenbrunn,<br/>
                                        Germany
                                        <br/><br/>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
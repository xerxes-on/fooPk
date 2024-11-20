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
                <tr>
                    <td class="header"
                        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 25px 0; text-align: center;">
                        <a href="{{ url(config('app.url_meinplan')) }}"
                           style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #bbbfc3; font-size: 19px; font-weight: bold; text-decoration: none; text-shadow: 0 1px 0 #ffffff;">
                            Dein Zugang ist freigeschaltet!
                        </a>
                    </td>
                </tr>

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0"
                        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; border-bottom: 1px solid #edeff2; border-top: 1px solid #edeff2; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                               style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; margin: 0 auto; padding: 0; width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">

                            <tr>
                                <td class="content-cell"
                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;  padding-left: 35px; padding-top: 5px;">
                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        English version below
                                    </p>
                                </td>
                            </tr>
                            <!-- Body content -->
                            <tr>
                                @php $actionUrl = url(config('app.url_meinplan').route('password.reset', $token, false)); @endphp
                                @php $actionText = 'Passwort anlegen'; @endphp

                                <td class="content-cell"
                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                                    <h1 style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 19px; font-weight: bold; margin-top: 0; text-align: left;">
                                        Hallo {{ $client?->first_name ?? 'Nutzer' }},</h1>

                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        du bist nun für das Foodpunk Ernährungsprogramm freigeschaltet.</p>

                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        Unter diesem Link kannst du dir dein Passwort für deinen Zugang anlegen:</p>

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
                                                                            {{ $actionText }}
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
                                        Bitte beachte, dass der Link nur 5 Tage gültig ist.
                                    </p>


                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        Viele Grüße<br>
                                        Dein Foodpunk Team
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="header"
                        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 25px 0; text-align: center;">
                        <a href="{{ url(config('app.url_meinplan')) }}"
                           style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #bbbfc3; font-size: 19px; font-weight: bold; text-decoration: none; text-shadow: 0 1px 0 #ffffff;">
                            Your access to the Foodpunk nutrition program
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0"
                        style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; border-bottom: 1px solid #edeff2; border-top: 1px solid #edeff2; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                               style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; margin: 0 auto; padding: 0; width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">

                            <!-- Body content -->
                            <tr>
                                @php $actionUrl = url(config('app.url_meinplan').route('password.reset', $token, false)); @endphp
                                @php $actionText = 'Set password'; @endphp

                                <td class="content-cell"
                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                                    <h1 style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 19px; font-weight: bold; margin-top: 0; text-align: left;">
                                        Hello {{ $client?->first_name ?? 'User' }},</h1>

                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        your access to the Foodpunk nutrition program has been activated.</p>

                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        Please set the password for your account with the following link: </p>

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
                                                                            {{ $actionText }}
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
                                        This link is available for 5 days.
                                    </p>


                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        Best<br>
                                        Your Foodpunk team
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0"
                               style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0 auto; padding: 0; text-align: center; width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">
                            <tr>
                                <td class="content-cell" align="center"
                                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; line-height: 1.5em; margin-top: 0; color: #aeaeae; font-size: 12px; text-align: left;">
                                        <b>Contact:</b><br/>
                                        <a href="mailto:info@foodpunk.de">info@foodpunk.de</a><br/>
                                        Chat on homepage<br/> <a href="tel:+498935762016">+49 89 35762016</a> (8-16
                                        h)<br/>
                                        <br/><br/>

                                        <b><a href="https://foodpunk.com/de/impressum/">@lang('email.footer.address')</a>:</b><br/>
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
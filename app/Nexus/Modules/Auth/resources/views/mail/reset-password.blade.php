@php($appName = config('app.name'))
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ __('auth::translate.password_forgot.subject') }}</title>
</head>
<body style="margin:0; padding:0; background:#f4f5f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color:#2b2e33; line-height:1.55;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; font-size:1px; color:transparent;">
        {{ __('auth::translate.password_forgot.preheader') }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f5f7; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; width:100%; background:#ffffff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); overflow:hidden;">
                    <tr>
                        <td style="padding:20px 32px; background:#F28B00; color:#ffffff; font-size:16px; font-weight:600; letter-spacing:0.3px;">
                            {{ $appName }}
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:36px 32px 8px;">
                            <div style="display:inline-block; width:64px; height:64px; line-height:64px; border-radius:50%; background:rgba(242,139,0,0.12); text-align:center; font-size:28px;">
                                🔐
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:8px 32px 8px;">
                            <h1 style="margin:0; font-size:22px; font-weight:700; color:#212529;">
                                {{ __('auth::translate.password_forgot.subject') }}
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 40px 0;">
                            <p style="margin:0 0 12px; font-size:15px; color:#2b2e33;">
                                {{ __('auth::translate.mail_confirmation.greeting', ['name' => $user->name]) }}
                            </p>
                            <p style="margin:0; font-size:15px; color:#2b2e33;">
                                {{ __('auth::translate.password_forgot.description') }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:28px 32px 8px;">
                            <a href="{{ $url }}"
                               style="display:inline-block; padding:14px 32px; background:#F28B00; color:#ffffff !important; text-decoration:none; border-radius:8px; font-weight:600; font-size:15px;">
                                {{ __('auth::translate.password_forgot.button') }}
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:8px 32px 24px;">
                            <p style="margin:0; font-size:13px; color:#6c757d;">
                                {{ __('auth::translate.password_forgot.expires_hint') }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 40px;">
                            <hr style="border:0; border-top:1px solid #eef0f2; margin:0;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 40px 8px;">
                            <p style="margin:0 0 6px; font-size:12px; color:#6c757d;">
                                {{ __('auth::translate.password_forgot.fallback_hint') }}
                            </p>
                            <p style="margin:0; font-size:12px; word-break:break-all;">
                                <a href="{{ $url }}" style="color:#F28B00; text-decoration:none;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 40px 28px;">
                            <p style="margin:0; font-size:12px; color:#6c757d;">
                                {{ __('auth::translate.password_forgot.ignore_note') }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 40px 24px; background:#fafbfc; border-top:1px solid #eef0f2;">
                            <p style="margin:0; font-size:12px; color:#6c757d; text-align:center;">
                                {{ __('auth::translate.password_forgot.footer', ['name' => $appName]) }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

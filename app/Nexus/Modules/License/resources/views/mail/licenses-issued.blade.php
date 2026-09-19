@php($appName = 'Nexus Store')
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Замовлення #{{ $order->id }} оплачено</title>
</head>
<body style="margin:0; padding:0; background:#f4f5f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color:#171717; line-height:1.55;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f5f7; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; width:100%; background:#ffffff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); overflow:hidden;">
                    <tr>
                        <td style="padding:20px 32px; background:#171717; color:#ffffff; font-size:16px; font-weight:600; letter-spacing:0.3px;">
                            {{ $appName }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 32px 8px;">
                            <h1 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#171717;">Дякуємо за покупку!</h1>
                            <p style="margin:0 0 4px; font-size:14px; color:#404040;">Вітаємо, {{ $user->name }}!</p>
                            <p style="margin:0; font-size:14px; color:#404040;">Замовлення #{{ $order->id }} оплачено. Ваші ліцензійні ключі вже активні:</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                @foreach($licenses as $license)
                                    <tr>
                                        <td style="padding:10px 14px; background:#fafafa; border:1px solid #e5e5e5; border-radius:8px;">
                                            <p style="margin:0 0 2px; font-size:13px; font-weight:600; color:#171717;">{{ $license->product?->name ?? '—' }}</p>
                                            <p style="margin:0; font-size:13px; font-family: monospace; color:#525252;">{{ $license->license_key }}</p>
                                        </td>
                                    </tr>
                                    <tr><td style="height:8px;"></td></tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:20px 32px 8px;">
                            <a href="{{ $url }}"
                               style="display:inline-block; padding:14px 32px; background:#171717; color:#ffffff !important; text-decoration:none; border-radius:8px; font-weight:600; font-size:14px;">
                                Перейти до моїх ліцензій
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px 8px;">
                            <p style="margin:0; font-size:12px; color:#737373;">Посилання на завантаження та активацію ліцензій доступні у вашому кабінеті за кнопкою вище.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 32px 24px; background:#fafafa; border-top:1px solid #eef0f2;">
                            <p style="margin:0; font-size:12px; color:#737373; text-align:center;">© {{ date('Y') }} {{ $appName }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $data['subject'] ?? 'Notification' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>

<body
    style="margin:0;background:#0b1222;font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#e5eaf3;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellspacing="0" cellpadding="0"
                    style="background:#0e1629;border:1px solid #202a3d;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px;border-bottom:1px solid #202a3d;background:#0c1426;">
                            <h1 style="margin:0;font-size:18px;line-height:1.3;">{{ $data['title'] ?? 'Notification' }}
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 24px;">
                            @if (!empty($data['greeting']))
                                <p style="margin:0 0 10px;">{{ $data['greeting'] }}</p>
                            @endif

                            @if (!empty($data['intro']))
                                <p style="margin:0 0 14px;color:#c7d2e0;">{{ $data['intro'] }}</p>
                            @endif

                            @if (!empty($data['password']))
                                <p style="margin:12px 0 8px;">Your Password:</p>
                                <div
                                    style="font-size:22px;font-weight:800;letter-spacing:3px;background:#0c1426;border:1px solid #22304a;border-radius:10px;display:inline-block;padding:10px 14px;">
                                    {{ $data['password'] }}
                                </div>
                            @endif


                            {{-- Optional action button --}}
                            @if (!empty($data['action_url']) && !empty($data['action_text']))
                                <div style="margin:18px 0 4px;">
                                    <a href="{{ $data['action_url'] }}"
                                        style="display:inline-block;background:linear-gradient(90deg,#1f9d6b,#2dbfa0);color:#0b1222;text-decoration:none;font-weight:700;border-radius:10px;padding:10px 16px;">
                                        {{ $data['action_text'] }}
                                    </a>
                                </div>
                                <p style="margin:10px 0 0;color:#7f8aa0;font-size:12px;word-break:break-all;">
                                    Or copy & paste this link: <br>
                                    <span style="color:#cfe0ff">{{ $data['action_url'] }}</span>
                                </p>
                            @endif

                            @if (!empty($data['outro']))
                                <p style="margin:16px 0 0;color:#aab6c7;">{{ $data['outro'] }}</p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="padding:16px 24px;border-top:1px solid #202a3d;background:#0c1426;color:#9aa3b2;font-size:12px;">
                            {{ $data['footer'] ?? config('app.name') . ' • ' . config('app.url') }}
                        </td>
                    </tr>
                </table>

                {{-- Plain-text fallback note --}}
                <div style="color:#7f8aa0;font-size:12px;margin-top:12px;">
                    If the button doesn’t work, use the link above.
                </div>
            </td>
        </tr>
    </table>
</body>

</html>

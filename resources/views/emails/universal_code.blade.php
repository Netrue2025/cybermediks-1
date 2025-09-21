<!doctype html>
<html lang="en">

<body
    style="font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#f6f9fc; margin:0; padding:24px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellspacing="0" cellpadding="0"
                    style="background:#ffffff;border-radius:12px;padding:28px;border:1px solid #e9eef5;">
                    <tr>
                        <td align="left">
                            <h2 style="margin:0 0 8px 0;color:#111827;font-size:20px;">
                                {{ $data['title'] ?? 'Notification' }}</h2>
                            <p style="margin:0 0 16px 0;color:#4b5563;font-size:14px;line-height:1.6;">
                                {{ $data['intro'] ?? '' }}
                            </p>

                            <div style="margin:18px 0; text-align:center;">
                                <div
                                    style="display:inline-block;border:1px dashed #cbd5e1;border-radius:10px;padding:14px 18px;">
                                    <span style="font-size:28px;letter-spacing:6px;font-weight:700;color:#111827;">
                                        {{ $data['code'] ?? '------' }}
                                    </span>
                                </div>
                            </div>

                            <p style="margin:12px 0 0 0;color:#6b7280;font-size:12px;">
                                {{ $data['footer'] ?? 'This code expires soon.' }}
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="color:#9ca3af;font-size:12px;margin:16px 0 0;">
                    {{ config('app.name') }} • {{ config('app.url') }}
                </p>
            </td>
        </tr>
    </table>
</body>

</html>

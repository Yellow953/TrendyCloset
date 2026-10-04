<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f1ee;font-family:Helvetica,Arial,sans-serif;color:#2b2523;">
    @isset($preheader)
        <div style="display:none;max-height:0;overflow:hidden;">{{ $preheader }}</div>
    @endisset
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f1ee;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">
                    <tr>
                        <td align="center" style="padding-bottom:24px;">
                            <a href="{{ url('/') }}" style="text-decoration:none;color:#2b2523;">
                                <img src="{{ asset('images/logo-192.png') }}" width="56" height="56" alt="" style="display:block;margin:0 auto 10px;border:0;">
                                <span style="font-family:Georgia,'Times New Roman',serif;font-size:24px;letter-spacing:0.04em;">{{ config('seo.brand') }}</span>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;border:1px solid #ded4cd;border-radius:10px;padding:32px 28px;">
                            @yield('body')
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:24px 8px 0;font-size:12px;line-height:1.6;color:#a08a80;">
                            {{ config('seo.brand_full') }} · {{ implode(', ', config('store.contact.address')) }}<br>
                            <a href="{{ url('/') }}" style="color:#a08a80;">{{ preg_replace('#^https?://#', '', url('/')) }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

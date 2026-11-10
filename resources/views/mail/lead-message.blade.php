<!doctype html>
<html lang="{{ $rtl ? 'ar' : 'en' }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:24px;background:#f6f6f2;font-family:Tahoma,Arial,sans-serif;color:#18181b">
<div style="max-width:600px;margin:0 auto;padding:28px;background:#ffffff;border-radius:8px;font-size:15px;line-height:1.9;text-align:{{ $rtl ? 'right' : 'left' }}">{!! nl2br(e($body)) !!}</div>
</body>
</html>

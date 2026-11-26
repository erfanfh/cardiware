<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Verification code</title>
</head>
<body style="font-family: sans-serif; text-align:center;">
<h2>Your verification code</h2>
<h1 style="letter-spacing:5px;">{{ $code }}</h1>
{{--<p>This code is valid for {{ config('auth.otp.expire') }} minutes.</p>--}}
</body>
</html>

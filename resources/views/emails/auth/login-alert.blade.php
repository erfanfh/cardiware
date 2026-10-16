@extends('emails.layout')

@section('title', 'اعلان ورود به حساب مودینگ‌وی')

@section('content')
    <h1 style="margin:0 0 20px;font-size:20px">ورود به حساب کاربری شما</h1>
    <p>سلام {{ $login->name }}،</p>
    <p>یک ورود موفق به حساب شما در مودینگ‌وی انجام شد.</p>
    <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="background:#f8fafc;border-radius:8px;font-size:14px">
        <tr><td>زمان (تهران)</td><td dir="ltr" style="text-align:right">{{ $login->time() }}</td></tr>
        <tr><td>نشانی IP</td><td dir="ltr" style="text-align:right">{{ $login->ip }}</td></tr>
        <tr><td>محل ورود</td><td>{{ $login->sourceLabel() }}</td></tr>
        @if($login->userAgent !== '')
            <tr><td>مرورگر / برنامه</td><td dir="ltr" style="text-align:right;word-break:break-all">{{ $login->userAgent }}</td></tr>
        @endif
    </table>
    <p style="margin-top:24px">اگر این ورود توسط شما انجام نشده است، رمز عبور حساب خود را تغییر دهید و با پشتیبانی مودینگ‌وی تماس بگیرید.</p>
@endsection

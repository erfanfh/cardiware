@extends('emails.front-layout')

@section('title', 'Sign-in alert Cardiware')

@section('content')
    <h1 style="margin:0 0 20px;font-size:20px">Sign-in to your account</h1>
    <p>Hello {{ $login->name }},</p>
    <p>A successful sign-in to your account at Cardiware occurred.</p>
    <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="background:#f8fafc;border-radius:8px;font-size:14px">
        <tr><td>Time (Tehran)</td><td dir="ltr" style="text-align:left">{{ $login->time() }}</td></tr>
        <tr><td>Address IP</td><td dir="ltr" style="text-align:left">{{ $login->ip }}</td></tr>
        <tr><td>Sign-in location</td><td>{{ $login->sourceLabel() }}</td></tr>
        @if($login->userAgent !== '')
            <tr><td>Browser / App</td><td dir="ltr" style="text-align:left;word-break:break-all">{{ $login->userAgent }}</td></tr>
        @endif
    </table>
    <p style="margin-top:24px">If you did not sign in, change your account password and contact support Cardiware for assistance.</p>
@endsection

@extends('errors.layout')

@section('title', 'چند لحظه دیگر برمی‌گردیم | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 500)

@section('label', 'Temporary Pause')

@section('box-title', 'داریم همه‌چیز را مرتب می‌کنیم ✨')

@section('box-subtitle', 'یک وقفه کوتاه پیش آمده است. چند لحظه دیگر دوباره امتحان کن؛ خیلی زود همه‌چیز مثل قبل آماده خواهد بود.')

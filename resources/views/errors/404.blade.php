@extends('errors.layout')

@section('title', 'صفحه پیدا نشد | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 404)

@section('label', 'Not Found')

@section('box-title', 'نتیجه‌ای یافت نشد!')

@section('box-subtitle', 'صفحه‌ای که دنبالش هستی پیدا نشد؛ ممکنه آدرس تغییر کرده یا صفحه حذف شده باشد.')

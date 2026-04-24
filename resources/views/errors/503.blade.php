@extends('errors.layout')

@section('title', 'سرویس موقتاً در دسترس نیست | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 503)

@section('label', 'Service Unavailable')

@section('box-title', 'در حال به‌روزرسانی هستیم')

@section('box-subtitle', 'سرویس موقتاً در دسترس نیست. به‌زودی دوباره برمی‌گردیم.')

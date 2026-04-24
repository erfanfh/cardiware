@extends('errors.layout')

@section('title', 'درخواست نامعتبر | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 400)

@section('label', 'Bad Request')

@section('box-title', 'درخواست قابل پردازش نیست')

@section('box-subtitle', 'درخواستی که ارسال شده معتبر نیست یا به‌درستی تکمیل نشده است. لطفاً دوباره تلاش کن.')

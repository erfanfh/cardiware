@extends('errors.layout')

@section('title', 'درخواست بیش از حد | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 429)

@section('label', 'Too Many Requests')

@section('box-title', 'کمی سریع‌تر از حد معمول بود!')

@section('box-subtitle', 'تعداد درخواست‌ها زیاد شده است. چند لحظه صبر کن و دوباره امتحان کن.')

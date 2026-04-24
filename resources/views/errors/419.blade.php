@extends('errors.layout')

@section('title', 'نشست منقضی شد | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 419)

@section('label', 'Page Expired')

@section('box-title', 'زمان صفحه به پایان رسیده')

@section('box-subtitle', 'برای امنیت بیشتر، نشست شما منقضی شده است. لطفاً صفحه را تازه‌سازی کرده و دوباره تلاش کن.')

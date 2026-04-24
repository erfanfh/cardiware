@extends('errors.layout')

@section('title', 'دسترسی محدود شده | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 403)

@section('label', 'Forbidden')

@section('box-title', 'اجازه دسترسی نداری')

@section('box-subtitle', 'متأسفانه اجازه مشاهده این صفحه برای حساب شما فعال نیست.')

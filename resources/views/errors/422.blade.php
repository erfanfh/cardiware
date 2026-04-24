@extends('errors.layout')

@section('title', 'اطلاعات نامعتبر | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 422)

@section('label', 'Unprocessable Entity')

@section('box-title', 'برخی اطلاعات نیاز به اصلاح دارند')

@section('box-subtitle', 'اطلاعات واردشده کامل یا صحیح نیست. لطفاً موارد را بررسی و دوباره ارسال کن.')

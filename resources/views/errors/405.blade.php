@extends('errors.layout')

@section('title', 'عملیات مجاز نیست | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 405)

@section('label', 'Method Not Allowed')

@section('box-title', 'این عملیات پشتیبانی نمی‌شود')

@section('box-subtitle', 'درخواست ارسال‌شده با این روش قابل انجام نیست. لطفاً مسیر درست را امتحان کن.')

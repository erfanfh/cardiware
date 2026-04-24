@extends('errors.layout')

@section('title', 'نیاز به ورود | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 401)

@section('label', 'Unauthorized')

@section('box-title', 'ابتدا وارد حساب خودت شو')

@section('box-subtitle', 'برای دسترسی به این بخش باید وارد حساب کاربری خودت شوی.')

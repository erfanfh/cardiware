@extends('errors.layout')

@section('title', 'Operation not allowed | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 405)

@section('label', 'Method Not Allowed')

@section('box-title', 'This operation is not supported')

@section('box-subtitle', 'This request method is not supported. Try the correct route.')

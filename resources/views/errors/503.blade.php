@extends('errors.layout')

@section('title', 'Service temporarily unavailable | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 503)

@section('label', 'Service Unavailable')

@section('box-title', 'We are updating the service')

@section('box-subtitle', 'The service is temporarily unavailable. We will be back soon.')

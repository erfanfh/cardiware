@extends('errors.layout')

@section('title', 'Too many requests | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 429)

@section('label', 'Too Many Requests')

@section('box-title', 'You are going a little too fast!')

@section('box-subtitle', 'Too many requests. Wait a moment and try again.')

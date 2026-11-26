@extends('errors.layout')

@section('title', 'We will be back shortly | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 500)

@section('label', 'Temporary Pause')

@section('box-title', 'We are getting everything ready ✨')

@section('box-subtitle', 'There has been a brief interruption. Try again shortly; everything will be ready soon.')

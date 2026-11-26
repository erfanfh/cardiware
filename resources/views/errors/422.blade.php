@extends('errors.layout')

@section('title', 'Invalid information | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 422)

@section('label', 'Unprocessable Entity')

@section('box-title', 'Some information needs correction')

@section('box-subtitle', 'The information is incomplete or incorrect. Review it and submit again.')

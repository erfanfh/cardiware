@extends('errors.layout')

@section('title', 'Access restricted | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 403)

@section('label', 'Forbidden')

@section('box-title', 'Access denied')

@section('box-subtitle', 'Your account does not have permission to view this page.')

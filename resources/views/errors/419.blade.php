@extends('errors.layout')

@section('title', 'Session expired | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 419)

@section('label', 'Page Expired')

@section('box-title', 'Page has expired')

@section('box-subtitle', 'For your security, your session has expired. Refresh the page and try again.')

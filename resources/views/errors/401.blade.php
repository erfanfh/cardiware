@extends('errors.layout')

@section('title', 'Sign-in required | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 401)

@section('label', 'Unauthorized')

@section('box-title', 'Sign in to your account first')

@section('box-subtitle', 'You must sign in to access this section.')

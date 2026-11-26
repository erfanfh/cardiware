@extends('errors.layout')

@section('title', 'Invalid request | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 400)

@section('label', 'Bad Request')

@section('box-title', 'Request cannot be processed')

@section('box-subtitle', 'The request is invalid or incomplete. Please try again.')

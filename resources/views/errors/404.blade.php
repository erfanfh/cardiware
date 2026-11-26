@extends('errors.layout')

@section('title', 'Page not found | ' . (get_setting('seo', 'site_name') ?: get_setting('general', 'title')))

@section('code', 404)

@section('label', 'Not Found')

@section('box-title', 'No results found!')

@section('box-subtitle', 'The page you are looking for was not found; its address may have changed or it may have been removed.')

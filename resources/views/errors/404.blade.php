@extends('errors.layout')
@section('title', 'Page not found')
@section('code', '404')
@section('heading', 'This page has wandered off the rail')
@section('message', 'The page you are looking for has moved, sold out, or never existed. Let us point you back to something in season.')
@section('secondary')
    <a href="{{ url('/shop') }}" class="btn btn-outline">Browse the shop</a>
@endsection

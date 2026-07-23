@extends('errors.layout')
@section('title', 'Please sign in')
@section('code', '401')
@section('heading', 'You need to sign in first')
@section('message', 'This area is for the back office. Sign in with your staff account to continue.')
@section('secondary')
    <a href="{{ route('login') }}" class="btn btn-outline">Sign in</a>
@endsection

@extends('errors.layout')
@section('title', 'Not allowed')
@section('code', '403')
@section('heading', 'This door is staff only')
@section('message')
    @if(!empty($exception) && $exception->getMessage())
        {{ $exception->getMessage() }}
    @else
        You do not have permission to view this page. If you believe this is a mistake, check you are signed in with the right account.
    @endif
@endsection

@extends('errors.layout')
@section('title', 'Page expired')
@section('code', '419')
@section('heading', 'Your session timed out')
@section('message', 'For your security the page sat idle a little too long. Head back and try again — nothing was lost.')
@section('secondary')
    <a href="javascript:history.back()" class="btn btn-outline">Go back</a>
@endsection

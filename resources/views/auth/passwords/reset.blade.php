@extends('layouts.auth')

@section('title', 'Choose a new password — Trendy Closet Admin')
@section('eyebrow', 'Account recovery')
@section('heading', 'Choose a new password')
@section('subheading', 'Pick something long and unique — at least eight characters.')

@section('form')
    <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-[18px]">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        @include('partials.auth-field', [
            'name' => 'email',
            'label' => 'Email address',
            'type' => 'email',
            'value' => $email ?? old('email'),
            'autocomplete' => 'email',
            'autofocus' => true,
        ])

        @include('partials.auth-field', [
            'name' => 'password',
            'label' => 'New password',
            'type' => 'password',
            'autocomplete' => 'new-password',
            'placeholder' => '••••••••',
        ])

        @include('partials.auth-field', [
            'name' => 'password_confirmation',
            'label' => 'Confirm new password',
            'type' => 'password',
            'autocomplete' => 'new-password',
            'placeholder' => '••••••••',
        ])

        <button type="submit" class="tc-btn-dark mt-2 w-full">Reset password</button>
    </form>
@endsection

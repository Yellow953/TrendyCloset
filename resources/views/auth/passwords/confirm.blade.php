@extends('layouts.auth')

@section('title', 'Confirm password — Trendy Closet Admin')
@section('eyebrow', 'Security check')
@section('heading', 'Confirm your password')
@section('subheading', "You're about to enter a protected area. Re-enter your password to continue.")

@section('form')
    <form method="POST" action="{{ route('password.confirm') }}" class="flex flex-col gap-[18px]">
        @csrf

        @include('partials.auth-field', [
            'name' => 'password',
            'label' => 'Password',
            'type' => 'password',
            'autocomplete' => 'current-password',
            'autofocus' => true,
            'placeholder' => '••••••••',
        ])

        <button type="submit" class="tc-btn-dark mt-2 w-full">Confirm</button>
    </form>

    <p class="mt-8 border-t border-line pt-6 text-[12.5px] font-light text-faint">
        <a href="{{ route('password.request') }}" class="tc-link">Forgot your password?</a>
    </p>
@endsection

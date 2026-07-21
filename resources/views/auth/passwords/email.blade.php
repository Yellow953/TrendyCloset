@extends('layouts.auth')

@section('title', 'Reset password — Trendy Closet Admin')
@section('eyebrow', 'Account recovery')
@section('heading', 'Reset your password')
@section('subheading', 'We will email you a secure link to choose a new password.')

@section('form')
    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-[18px]">
        @csrf

        @include('partials.auth-field', [
            'name' => 'email',
            'label' => 'Email address',
            'type' => 'email',
            'value' => old('email'),
            'autocomplete' => 'email',
            'autofocus' => true,
            'placeholder' => 'you@trendycloset.com',
        ])

        <button type="submit" class="tc-btn-dark mt-2 w-full">Send reset link</button>
    </form>

    <p class="mt-8 border-t border-line pt-6 text-[12.5px] font-light text-faint">
        Remembered it? <a href="{{ route('login') }}" class="tc-link">Back to sign in</a>
    </p>
@endsection

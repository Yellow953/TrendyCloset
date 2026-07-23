@extends('layouts.auth')

@section('title', 'Reset password — Trendy Closet Admin')
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

        <button type="submit" class="ad-btn-primary mt-2 w-full py-3">Send reset link</button>
    </form>

    <p class="mt-8 border-t border-slate-200 pt-6 text-[12.5px] font-normal text-slate-400">
        Remembered it? <a href="{{ route('login') }}" class="font-medium text-slate-900 underline underline-offset-4 transition-colors hover:text-slate-600">Back to sign in</a>
    </p>
@endsection

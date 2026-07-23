@extends('layouts.auth')

@section('title', 'Confirm password — Trendy Closet Admin')
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

        <button type="submit" class="ad-btn-primary mt-2 w-full py-3">Confirm</button>
    </form>

    <p class="mt-8 border-t border-slate-200 pt-6 text-[12.5px] font-normal text-slate-400">
        <a href="{{ route('password.request') }}" class="font-medium text-slate-900 underline underline-offset-4 transition-colors hover:text-slate-600">Forgot your password?</a>
    </p>
@endsection

@extends('layouts.auth')

@section('title', 'Sign in — Trendy Closet Admin')
@section('heading', 'Sign in')
@section('subheading', 'Enter your credentials to open the Trendy Closet back office.')

@section('form')
    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-[18px]">
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

        @include('partials.auth-field', [
            'name' => 'password',
            'label' => 'Password',
            'type' => 'password',
            'autocomplete' => 'current-password',
            'placeholder' => '••••••••',
        ])

        <div class="flex items-center justify-between pt-1">
            <label for="remember" class="flex cursor-pointer items-center gap-2.5 text-[13.5px] font-normal text-slate-500">
                <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
                       class="h-4 w-4 accent-slate-900">
                Keep me signed in
            </label>

            <a href="{{ route('password.request') }}" class="text-[13px] font-normal text-slate-500 underline underline-offset-4 transition-colors hover:text-slate-700">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="ad-btn-primary mt-2 w-full py-3">Sign in</button>
    </form>

    <p class="mt-8 border-t border-slate-200 pt-6 text-[12.5px] leading-relaxed font-normal text-slate-400">
        Accounts are issued by the site administrator — there is no public sign-up.
    </p>
@endsection

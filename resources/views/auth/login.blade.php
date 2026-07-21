@extends('layouts.auth')

@section('title', 'Sign in — Trendy Closet Admin')
@section('eyebrow', 'Staff access')
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
            <label for="remember" class="flex cursor-pointer items-center gap-2.5 text-[13.5px] font-light text-muted-2">
                <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
                       class="h-4 w-4 accent-blush">
                Keep me signed in
            </label>

            <a href="{{ route('password.request') }}" class="text-[13px] font-light text-muted-2 underline underline-offset-4 transition-colors hover:text-blush">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="tc-btn-dark mt-2 w-full">Sign in</button>
    </form>

    <p class="mt-8 border-t border-line pt-6 text-[12.5px] leading-relaxed font-light text-faint">
        Accounts are issued by the site administrator — there is no public sign-up.
    </p>
@endsection

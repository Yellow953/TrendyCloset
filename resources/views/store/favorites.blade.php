@extends('layouts.storefront')

@section('content')
    <div class="bg-cream px-5 md:px-10 py-7">
        <h1 class="text-[32px] font-normal">Your Favourites</h1>
        <div class="mt-1 text-[13px] font-light text-muted">Saved on this device — no account needed.</div>
    </div>

    <div class="px-5 md:px-10 py-9">
        @if($products->isEmpty())
            <div class="border border-line bg-cream-3 px-6 py-20 text-center">
                <div class="text-[22px] font-normal">Nothing saved yet</div>
                <div class="mt-2 text-[14.5px] font-light text-muted-2">Tap ♡ on a piece you like and it will wait for you here.</div>
                <a href="{{ route('listing') }}" class="tc-btn-dark mt-6">Browse the shop</a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-[22px] md:grid-cols-4">
                @foreach($products as $p)
                    @include('partials.product-card', ['p' => $p, 'h' => 'h-[300px] sm:h-[360px]'])
                @endforeach
            </div>
        @endif
    </div>
@endsection

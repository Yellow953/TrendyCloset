@extends('layouts.storefront')

@section('title', 'Women — Trendy Closet')

@section('content')
    <div class="flex items-baseline justify-between bg-cream px-5 md:px-10 py-7">
        <div>
            <h1 class="text-[32px] font-normal">Women</h1>
            <div class="mt-1 text-[13px] font-light text-muted">Home / Women</div>
        </div>
        <div class="hidden text-[13.5px] font-light text-muted-2 sm:block">Showing 1–8 of 54 products</div>
    </div>

    <div class="flex flex-col gap-9 px-5 md:px-10 py-9 lg:flex-row">
        {{-- Sidebar --}}
        <aside class="flex w-full flex-col gap-7 lg:w-[230px] lg:flex-none">
            <div>
                <div class="mb-3 border-b border-line pb-2.5 text-[15px] font-medium tracking-[0.06em]">CATEGORY</div>
                @foreach($filterCats as $f)
                    <a href="#" class="flex justify-between text-[14px] font-light leading-[2.1] text-muted-3 hover:text-blush"><span>{{ $f['name'] }}</span><span class="text-faint">{{ $f['n'] }}</span></a>
                @endforeach
            </div>
            <div>
                <div class="mb-3.5 border-b border-line pb-2.5 text-[15px] font-medium tracking-[0.06em]">SIZE</div>
                <div class="flex flex-wrap gap-2">
                    @foreach($sizes as $s)
                        <div class="w-11 border border-line-2 py-1.5 text-center text-[13px] font-normal transition-colors hover:border-blush hover:text-blush">{{ $s }}</div>
                    @endforeach
                </div>
            </div>
            <div>
                <div class="mb-3.5 border-b border-line pb-2.5 text-[15px] font-medium tracking-[0.06em]">COLOR</div>
                <div class="flex gap-2.5">
                    <div class="h-6 w-6 rounded-full bg-tan outline-2 outline-offset-2 outline-blush"></div>
                    <div class="h-6 w-6 rounded-full bg-ink"></div>
                    <div class="h-6 w-6 rounded-full border border-line-2 bg-white"></div>
                    <div class="h-6 w-6 rounded-full bg-[#8a9a8e]"></div>
                    <div class="h-6 w-6 rounded-full bg-[#b8c4cf]"></div>
                    <div class="h-6 w-6 rounded-full bg-blush"></div>
                </div>
            </div>
            <div>
                <div class="mb-3.5 border-b border-line pb-2.5 text-[15px] font-medium tracking-[0.06em]">PRICE</div>
                <div class="relative mx-1 my-2.5 h-[3px] bg-line">
                    <div class="absolute inset-y-0 left-[10%] right-[30%] bg-blush"></div>
                    <div class="absolute -top-[5px] left-[10%] h-[13px] w-[13px] rounded-full bg-blush"></div>
                    <div class="absolute -top-[5px] right-[30%] h-[13px] w-[13px] rounded-full bg-blush"></div>
                </div>
                <div class="mt-2.5 text-[13px] font-light text-muted-2">$10 — $120</div>
            </div>
            <div class="relative h-[300px] overflow-hidden bg-tan">
                <img src="{{ $sideBanner['img'] }}" alt="Sale up to 40% off" class="absolute inset-0 h-full w-full object-cover">
                <div class="pointer-events-none absolute inset-0 flex flex-col justify-end gap-1.5 p-6">
                    <div class="text-[22px] font-normal leading-[1.2]">Sale up to<br>40% off</div>
                    <div class="text-[13px] font-medium text-blush underline underline-offset-2">Shop Sale</div>
                </div>
            </div>
        </aside>

        {{-- Grid --}}
        <div class="flex-1">
            <div class="mb-[22px] flex flex-wrap items-center justify-between gap-3">
                <div class="flex gap-2.5 text-[13px] font-normal">
                    <div class="bg-ink px-4 py-2 text-white">All</div>
                    <div class="border border-line-2 px-4 py-2">New in</div>
                    <div class="border border-line-2 px-4 py-2">Bestsellers</div>
                    <div class="border border-line-2 px-4 py-2">Sale</div>
                </div>
                <div class="border border-line-2 px-4 py-2 text-[13px] font-light text-muted-2">Sort: Most popular ▾</div>
            </div>
            <div class="grid grid-cols-2 gap-[22px] md:grid-cols-3">
                @foreach($listing as $p)
                    @include('partials.product-card', ['p' => $p, 'h' => 'h-[280px]'])
                @endforeach
            </div>
            <div class="mt-9 flex justify-center gap-2 text-[14px] font-normal">
                <div class="flex h-[38px] w-[38px] items-center justify-center bg-ink text-white">1</div>
                <div class="flex h-[38px] w-[38px] items-center justify-center border border-line-2 hover:border-blush">2</div>
                <div class="flex h-[38px] w-[38px] items-center justify-center border border-line-2 hover:border-blush">3</div>
                <div class="flex h-[38px] w-[38px] items-center justify-center border border-line-2 hover:border-blush">→</div>
            </div>
        </div>
    </div>
@endsection

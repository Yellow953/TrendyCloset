@extends('layouts.storefront')

@section('title', 'Shipping & Policies — Trendy Closet')

@section('content')
    <div class="bg-cream px-8 py-12 text-center md:px-16">
        <div class="text-[34px] font-normal">Shipping &amp; Policies</div>
    </div>

    <div class="flex flex-col gap-10 px-8 py-12 md:px-16 lg:flex-row">
        {{-- Sidebar nav --}}
        <div class="flex flex-col gap-0.5 lg:flex-[0_0_240px]">
            <div class="bg-ink px-[18px] py-3.5 text-[14px] font-medium text-white">Shipping</div>
            <div class="border-b border-line px-[18px] py-3.5 text-[14px] text-muted-3 hover:text-blush">Returns &amp; Refunds</div>
            <div class="border-b border-line px-[18px] py-3.5 text-[14px] text-muted-3 hover:text-blush">Size Guide</div>
            <div class="border-b border-line px-[18px] py-3.5 text-[14px] text-muted-3 hover:text-blush">Privacy Policy</div>
            <div class="border-b border-line px-[18px] py-3.5 text-[14px] text-muted-3 hover:text-blush">Terms of Service</div>
        </div>

        {{-- Content --}}
        <div class="flex flex-1 flex-col gap-[22px]">
            <div>
                <div class="mb-2 text-[17px] font-medium">Shipping</div>
                <div class="text-[14.5px] font-light leading-[1.75] text-muted-3">Free worldwide shipping on orders over $150. Standard delivery takes 3–5 business days; express delivery (1–2 business days) is available for $9.00. Orders ship Monday–Friday, tracking sent by email once dispatched.</div>
            </div>
            <div>
                <div class="mb-2 text-[17px] font-medium">Returns &amp; Refunds</div>
                <div class="text-[14.5px] font-light leading-[1.75] text-muted-3">Unworn items with tags attached can be returned within 30 days of delivery for a full refund. Sale items are final sale unless faulty. Start a return from your account or by emailing hello@trendycloset.com.</div>
            </div>
            <div>
                <div class="mb-2 text-[17px] font-medium">Size Guide</div>
                <div class="text-[14.5px] font-light leading-[1.75] text-muted-3">Our pieces run true to size. Full measurement charts for women's, men's and kids' fits are listed on each product page — DM us on Instagram if you need a personal sizing recommendation.</div>
            </div>
        </div>
    </div>
@endsection

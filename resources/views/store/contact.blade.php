@extends('layouts.storefront')

@section('title', 'Contact — Trendy Closet')

@section('content')
    <div class="bg-cream px-8 py-12 text-center md:px-16">
        <div class="text-[34px] font-normal">Get in touch</div>
        <div class="mt-2 text-[14.5px] font-light text-muted">Questions about an order, sizing, or a collab? We'd love to hear from you.</div>
    </div>

    <div class="flex flex-col gap-14 px-8 py-14 md:px-16 lg:flex-row">
        {{-- Form --}}
        <form class="flex flex-1 flex-col gap-[18px]">
            <input placeholder="Your name" class="tc-input">
            <input type="email" placeholder="Email address" class="tc-input">
            <input placeholder="Order number (optional)" class="tc-input">
            <textarea placeholder="Message" rows="4" class="tc-input h-[120px] resize-none"></textarea>
            <button type="submit" class="w-[180px] bg-ink py-4 text-center text-[14px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush">Send Message</button>
        </form>

        {{-- Info --}}
        <div class="flex flex-col gap-6 lg:flex-[0_0_340px]">
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">EMAIL</div><div class="text-[15px] font-light">hello@trendycloset.com</div></div>
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">INSTAGRAM</div><div class="text-[15px] font-light">@trendycloset.byleilakonsol</div></div>
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">HOURS</div><div class="text-[15px] font-light">Mon–Sat, 9am–6pm</div></div>
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">RESPONSE TIME</div><div class="text-[15px] font-light">Within 24 hours</div></div>
        </div>
    </div>
@endsection

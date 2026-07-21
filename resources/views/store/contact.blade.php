@extends('layouts.storefront')

@section('title', 'Contact — Trendy Closet')

@section('content')
    @php($whatsapp = config('store.whatsapp'))

    <div class="bg-cream px-8 py-12 text-center md:px-16">
        <div class="text-[12px] font-medium tracking-[0.32em] text-blush-soft">WE'D LOVE TO HEAR FROM YOU</div>
        <div class="mt-2.5 text-[34px] font-normal">Get in touch</div>
        <div class="mt-2 text-[14.5px] font-light text-muted">Questions about an order, sizing, or a collab? We answer every message within 24 hours.</div>
    </div>

    <div class="flex flex-col gap-14 px-8 py-14 md:px-16 lg:flex-row">
        {{-- Form — writes to `contact_messages` for the back-office CRM. --}}
        <form method="POST" action="{{ route('contact.send') }}" class="flex flex-1 flex-col gap-[18px]">
            @csrf
            <div>
                <input name="name" value="{{ old('name') }}" placeholder="Your name" required class="tc-input">
                @error('name')<div class="mt-1 text-[12.5px] text-blush">{{ $message }}</div>@enderror
            </div>
            <div>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email address" required class="tc-input">
                @error('email')<div class="mt-1 text-[12.5px] text-blush">{{ $message }}</div>@enderror
            </div>
            <div>
                <input name="subject" value="{{ old('subject') }}" placeholder="Order number or subject (optional)" class="tc-input">
                @error('subject')<div class="mt-1 text-[12.5px] text-blush">{{ $message }}</div>@enderror
            </div>
            <div>
                <textarea name="message" rows="4" placeholder="Message" required class="tc-input h-[140px] resize-none">{{ old('message') }}</textarea>
                @error('message')<div class="mt-1 text-[12.5px] text-blush">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="w-full bg-ink py-4 text-center text-[14px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush sm:w-[180px]">Send Message</button>
        </form>

        {{-- Info --}}
        <div class="flex flex-col gap-6 lg:flex-[0_0_340px]">
            <div>
                <div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">EMAIL</div>
                <a href="mailto:hello@trendycloset.com" class="text-[15px] font-light transition-colors hover:text-blush">hello@trendycloset.com</a>
            </div>
            @if(! empty($whatsapp['number']))
                <div>
                    <div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">WHATSAPP</div>
                    <a href="https://wa.me/{{ $whatsapp['number'] }}" target="_blank" rel="noopener" class="text-[15px] font-light transition-colors hover:text-blush">Chat with us</a>
                </div>
            @endif
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">INSTAGRAM</div><div class="text-[15px] font-light">@trendycloset.byleilakonsol</div></div>
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">HOURS</div><div class="text-[15px] font-light">Mon–Sat, 9am–6pm</div></div>
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">RESPONSE TIME</div><div class="text-[15px] font-light">Within 24 hours</div></div>

            {{-- Answers that don't need a reply --}}
            <div class="border-t border-line pt-6">
                <div class="mb-3 text-[14px] font-medium tracking-[0.06em] text-blush">QUICK ANSWERS</div>
                <div class="flex flex-col gap-2 text-[14.5px] font-light text-muted-2">
                    <a href="{{ route('policies', 'shipping') }}" class="transition-colors hover:text-blush">How long does delivery take?</a>
                    <a href="{{ route('policies', 'returns') }}" class="transition-colors hover:text-blush">How do I return something?</a>
                    <a href="{{ route('policies', 'size-guide') }}" class="transition-colors hover:text-blush">Which size should I order?</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.storefront')

@section('content')
    @php
        $whatsapp = config('store.whatsapp');
        $contact = config('store.contact');
        $arrow = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" class="ml-1.5 inline-block h-[13px] w-[13px] align-[-1px] text-blush transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>';
    @endphp

    <div class="bg-cream px-8 py-12 text-center md:px-16">
        <div class="text-[12px] font-medium tracking-[0.32em] text-blush-soft">WE'D LOVE TO HEAR FROM YOU</div>
        <h1 class="mt-2.5 text-[34px] font-normal">Get in touch</h1>
        <div class="mt-2 text-[14.5px] font-light text-muted">Questions about an order, sizing, or a collab? We answer every message {{ $contact['response_time'] }}.</div>
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
            <button type="submit" class="tc-btn-dark w-full text-[14px] sm:w-[180px]">Send Message</button>
        </form>

        {{-- Info --}}
        <div class="flex flex-col gap-6 lg:flex-[0_0_340px]">
            <div>
                <div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">EMAIL</div>
                <a href="mailto:{{ config('seo.email') }}" class="group inline-flex items-start gap-3 text-[15px] font-light transition-colors hover:text-blush"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="mt-[3px] h-[18px] w-[18px] shrink-0 text-blush"><rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="m3.5 6.5 8.5 6 8.5-6"/></svg><span class="break-all">{{ config('seo.email') }}{!! $arrow !!}</span></a>
            </div>
            @if(! empty($whatsapp['number']))
                <div>
                    <div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">WHATSAPP</div>
                    <a href="https://wa.me/{{ $whatsapp['number'] }}?text={{ rawurlencode($whatsapp['message']) }}" target="_blank" rel="noopener" class="group inline-flex items-start gap-3 text-[15px] font-light transition-colors hover:text-blush"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="mt-[3px] h-[18px] w-[18px] shrink-0 text-blush"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.39a9.86 9.86 0 0 0 4.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.02h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.11.82.83-3.03-.2-.31a8.19 8.19 0 0 1-1.26-4.37c0-4.54 3.7-8.23 8.23-8.23a8.17 8.17 0 0 1 5.82 2.42 8.18 8.18 0 0 1 2.4 5.82c0 4.54-3.69 8.21-8.22 8.21Zm4.51-6.16c-.25-.12-1.46-.72-1.69-.8-.23-.09-.39-.13-.56.12-.16.25-.64.8-.79.97-.14.16-.29.18-.54.06-.25-.13-1.04-.39-1.99-1.23-.73-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.44.13-.15.17-.25.25-.42.08-.16.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.42l-.47-.01c-.16 0-.43.06-.65.31-.23.25-.86.84-.86 2.05s.88 2.38 1 2.54c.12.17 1.73 2.64 4.2 3.7.59.25 1.04.4 1.4.52.59.18 1.12.16 1.54.1.47-.07 1.46-.6 1.66-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.29Z"/></svg><span>{{ $contact['phone_display'] }}{!! $arrow !!}</span></a>
                </div>
            @endif
            <div>
                <div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">VISIT</div>
                <a href="{{ $contact['map_url'] }}" target="_blank" rel="noopener" class="group inline-flex items-start gap-3 text-[15px] font-light leading-[1.7] transition-colors hover:text-blush"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="mt-[3px] h-[18px] w-[18px] shrink-0 text-blush"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg><span>{!! implode('<br>', array_map('e', $contact['address'])) !!}{!! $arrow !!}</span></a>
            </div>
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">INSTAGRAM</div><a href="{{ config('seo.social.instagram') }}" target="_blank" rel="noopener" class="group inline-flex items-start gap-3 text-[15px] font-light transition-colors hover:text-blush"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="mt-[3px] h-[18px] w-[18px] shrink-0 text-blush"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.8" fill="currentColor" stroke="none"/></svg><span>@trendycloset.byleilakonsol{!! $arrow !!}</span></a></div>
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">HOURS</div><div class="text-[15px] font-light leading-[1.8]">{!! implode('<br>', array_map('e', \App\Models\OpeningHour::lines())) !!}</div></div>
            <div><div class="mb-2 text-[14px] font-medium tracking-[0.06em] text-blush">RESPONSE TIME</div><div class="text-[15px] font-light">{{ ucfirst($contact['response_time']) }}</div></div>
        </div>
    </div>
@endsection

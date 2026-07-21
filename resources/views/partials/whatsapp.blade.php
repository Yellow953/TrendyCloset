{{-- Floating WhatsApp button, on every storefront page. Number comes from
     config/store.php (WHATSAPP_NUMBER); an empty number hides the button.
     It lifts above the product page's sticky buy bar via .has-sticky-buy. --}}
@php($whatsapp = config('store.whatsapp'))

@if(! empty($whatsapp['number']))
    <a href="https://wa.me/{{ $whatsapp['number'] }}?text={{ rawurlencode($whatsapp['message']) }}"
       target="_blank" rel="noopener"
       aria-label="Chat with us on WhatsApp"
       class="tc-whatsapp fixed right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-[0_8px_24px_rgba(37,211,102,.4)] transition-transform hover:scale-105 md:right-8">
        <svg viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7" aria-hidden="true">
            <path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.39a9.86 9.86 0 0 0 4.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.02h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.11.82.83-3.03-.2-.31a8.19 8.19 0 0 1-1.26-4.37c0-4.54 3.7-8.23 8.23-8.23a8.17 8.17 0 0 1 5.82 2.42 8.18 8.18 0 0 1 2.4 5.82c0 4.54-3.69 8.21-8.22 8.21Zm4.51-6.16c-.25-.12-1.46-.72-1.69-.8-.23-.09-.39-.13-.56.12-.16.25-.64.8-.79.97-.14.16-.29.18-.54.06-.25-.13-1.04-.39-1.99-1.23-.73-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.44.13-.15.17-.25.25-.42.08-.16.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.42l-.47-.01c-.16 0-.43.06-.65.31-.23.25-.86.84-.86 2.05s.88 2.38 1 2.54c.12.17 1.73 2.64 4.2 3.7.59.25 1.04.4 1.4.52.59.18 1.12.16 1.54.1.47-.07 1.46-.6 1.66-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.29Z"/>
        </svg>
    </a>
@endif

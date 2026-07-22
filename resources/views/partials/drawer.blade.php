{{-- One slide-over, shared by the bag and favourites. The shell ships empty;
     app.js fetches whichever fragment the trigger points at (its
     data-drawer-open URL) when it opens, and re-fetches after every change —
     so the panel is always live. With JS off the header icons are still plain
     links to /bag and /favorites. --}}
<div data-drawer-overlay class="fixed inset-0 z-50 bg-ink/40 backdrop-blur-[2px]" aria-hidden="true"></div>

<aside data-drawer role="dialog" aria-modal="true" aria-label="Bag and favourites"
       class="fixed inset-y-0 right-0 z-50 w-[92vw] max-w-[420px] bg-white shadow-[-16px_0_44px_rgba(43,37,35,.18)]">
    <div data-drawer-body class="h-full">
        <div class="flex h-full items-center justify-center text-[14px] font-light text-muted-2">Loading…</div>
    </div>
</aside>

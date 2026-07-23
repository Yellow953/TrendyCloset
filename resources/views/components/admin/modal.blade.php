@props([
    'id',
    'title' => null,
    'subtitle' => null,
    'width' => 'max-w-[520px]',
    'autoopen' => false,
])

{{-- A back-office dialog.

     Opened by any [data-modal-open="<id>"] on the page and closed by ESC, the
     backdrop, or [data-modal-close] — see `initModals()` in app.js. Kept in the
     DOM rather than fetched, so the form inside is a plain server-rendered form
     that posts and redirects like every other one here. --}}
{{-- `autoopen` re-opens a dialog whose form came back with validation errors,
     so nobody has to hunt for the modal they were just in. --}}
<div data-modal="{{ $id }}" @if($autoopen) data-modal-autoopen @endif
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     role="dialog" aria-modal="true"
     @if($title) aria-labelledby="{{ $id }}-title" @endif>

    <div data-modal-backdrop class="ad-modal-backdrop absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]"></div>

    <div {{ $attributes->merge(['class' => 'ad-modal-card relative w-full '.$width.' overflow-hidden rounded-2xl bg-white shadow-[0_24px_60px_rgba(15,23,42,.28)]']) }}>
        @if($title)
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 id="{{ $id }}-title" class="font-semibold text-[21px] leading-tight font-normal">{{ $title }}</h2>
                    @if($subtitle)
                        <p class="mt-1 text-[12.5px] leading-relaxed font-normal text-slate-500">{{ $subtitle }}</p>
                    @endif
                </div>
                <button type="button" data-modal-close
                        class="-mt-1 -mr-2 flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-[16px] text-slate-400 transition-colors hover:bg-slate-50 hover:text-slate-800"
                        aria-label="Close">✕</button>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>

@props([
    'id',
    'action',
    'title' => 'Are you sure?',
    'body' => 'This cannot be undone.',
    'confirm' => 'Delete',
    'method' => 'DELETE',
    'tone' => 'danger',
])

{{-- The destructive-action dialog — a SweetAlert-style centred prompt with a
     warning glyph. Every delete in the back office goes through one of these
     rather than a bare button, because the list rows are dense enough that a
     mis-click is a real risk. Opened by [data-modal-open="<id>"]. --}}
@php
    $ring = $tone === 'danger' ? 'bg-rose-50 text-rose-500 ring-rose-100' : 'bg-amber-50 text-amber-500 ring-amber-100';
    $cta = $tone === 'danger' ? 'ad-btn-danger' : 'ad-btn-primary';
@endphp

<x-admin.modal :id="$id" width="max-w-[400px]">
    <form method="POST" action="{{ $action }}" class="ad-confirm px-6 pt-8 pb-6 text-center">
        @csrf
        @method($method)

        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full ring-8 {{ $ring }}">
            <x-admin.icon name="warning" class="h-7 w-7" />
        </span>

        <h2 class="mt-5 text-[18px] leading-snug font-bold tracking-[-0.01em] text-slate-900">{{ $title }}</h2>
        <p class="mx-auto mt-2 max-w-[46ch] text-[13px] leading-relaxed font-normal text-slate-500">{{ $body }}</p>

        {{ $slot }}

        <div class="mt-6 flex justify-center gap-2.5">
            <button type="button" data-modal-close class="ad-btn min-w-[110px]">Cancel</button>
            <button type="submit" class="{{ $cta }} min-w-[110px]">{{ $confirm }}</button>
        </div>
    </form>
</x-admin.modal>

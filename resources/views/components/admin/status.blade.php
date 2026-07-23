@props(['status'])

{{-- One order status, rendered the same way everywhere it appears. --}}
<span class="ad-badge {{ $status->classes() }}">{{ $status->label() }}</span>

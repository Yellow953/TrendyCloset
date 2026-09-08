@props(['status'])

{{-- One order status, rendered the same way everywhere it appears. --}}
<span class="bo-badge {{ $status->classes() }}">{{ $status->label() }}</span>

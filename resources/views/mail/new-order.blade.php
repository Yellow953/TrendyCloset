@extends('mail.layout', [
    'title' => 'New order '.$order->order_number,
    'preheader' => $order->ship_name.' · '.$order->ship_phone.' · '.\App\Models\Product::money($order->grand_total),
])

@section('body')
    @php
        $wa = ltrim(\App\Models\Customer::normalizePhone($order->ship_phone), '+');
        $label = 'font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:#a08a80;';
    @endphp

    <div style="{{ $label }}">New order</div>
    <h1 style="margin:6px 0 0;font-size:22px;font-weight:bold;">{{ $order->order_number }}</h1>
    <div style="margin-top:4px;font-size:13px;color:#a08a80;">{{ $order->created_at->format('D j M Y, g:ia') }}</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;background:#faf5f2;border-radius:8px;">
        <tr>
            <td style="padding:16px 18px;font-size:14px;line-height:1.7;">
                <div style="{{ $label }}">Customer</div>
                <div style="margin-top:6px;font-size:15px;">{{ $order->ship_name }}</div>
                <div><a href="https://wa.me/{{ $wa }}" style="color:#1d8a76;">{{ $order->ship_phone }}</a> (WhatsApp)</div>
                @if($order->email)
                    <div><a href="mailto:{{ $order->email }}" style="color:#6b5d57;">{{ $order->email }}</a></div>
                @endif
                @if($order->notes)
                    <div style="margin-top:10px;{{ $label }}">Note</div>
                    <div style="margin-top:4px;color:#6b5d57;">{!! nl2br(e($order->notes)) !!}</div>
                @endif
            </td>
        </tr>
    </table>

    @include('mail.partials.order-summary')

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:28px;">
        <tr>
            <td style="background:#2b2523;border-radius:6px;">
                <a href="{{ route('admin.orders.show', $order) }}" style="display:inline-block;padding:12px 22px;font-size:13px;letter-spacing:0.12em;text-transform:uppercase;color:#ffffff;text-decoration:none;">Open order</a>
            </td>
            <td style="width:10px;"></td>
            <td style="border:1px solid #1d8a76;border-radius:6px;">
                <a href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Hi '.$order->ship_name.', about your Trendy Closet order '.$order->order_number.'…') }}" style="display:inline-block;padding:11px 20px;font-size:13px;letter-spacing:0.12em;text-transform:uppercase;color:#1d8a76;text-decoration:none;">WhatsApp</a>
            </td>
        </tr>
    </table>
@endsection

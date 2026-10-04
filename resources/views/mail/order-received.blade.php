@extends('mail.layout', [
    'title' => 'Order '.$order->order_number,
    'preheader' => 'Order '.$order->order_number.' confirmed. Payment is due on delivery.',
])

@section('body')
    <h1 style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:28px;font-weight:normal;text-align:center;">Thank you for your order</h1>
    <p style="margin:16px 0 0;font-size:14.5px;line-height:1.7;color:#6b5d57;">
        Dear {{ ucfirst(\Illuminate\Support\Str::before(trim($order->ship_name), ' ')) }},
    </p>
    <p style="margin:12px 0 0;font-size:14.5px;line-height:1.7;color:#6b5d57;">
        We have received your order <strong style="color:#2b2523;">{{ $order->order_number }}</strong> and are now preparing it.
        A member of our team will contact you shortly on WhatsApp at
        <strong style="color:#2b2523;">{{ $order->ship_phone }}</strong> to confirm your order and schedule delivery.
    </p>
    <p style="margin:16px 0 0;padding:12px 16px;background:#faf5f2;border-radius:6px;font-size:14px;line-height:1.6;color:#2b2523;">
        <strong>Payment on delivery</strong> — you will pay {{ \App\Models\Product::money($order->grand_total) }} when your order arrives.
    </p>

    @include('mail.partials.order-summary')

    <p style="margin:24px 0 0;font-size:13.5px;line-height:1.7;color:#6b5d57;">
        If you have any questions, simply reply to this email or contact us on WhatsApp at
        <a href="https://wa.me/{{ config('store.whatsapp.number') }}" style="color:#93524d;">{{ config('store.contact.phone_display') }}</a>.
    </p>
    <p style="margin:16px 0 0;font-size:13.5px;line-height:1.7;color:#6b5d57;">Kind regards,<br>{{ config('seo.founder') }} · {{ config('seo.brand') }}</p>
@endsection

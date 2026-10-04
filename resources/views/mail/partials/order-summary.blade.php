@php
    $money = fn ($v) => \App\Models\Product::money($v);
    $row = 'font-size:14px;line-height:2;color:#6b5d57;';
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;">
    @foreach($order->items as $item)
        @php
            $image = $item->variant?->product?->image_url;
        @endphp
        <tr>
            <td width="64" style="padding:10px 14px 10px 0;border-bottom:1px solid #efe7e2;vertical-align:middle;">
                @if($image)
                    <img src="{{ url($image) }}" width="64" height="64" alt="{{ $item->product_name }}" style="display:block;width:64px;height:64px;object-fit:cover;border-radius:6px;background:#faf5f2;border:0;">
                @else
                    <div style="width:64px;height:64px;border-radius:6px;background:#faf5f2;"></div>
                @endif
            </td>
            <td style="padding:10px 0;border-bottom:1px solid #efe7e2;vertical-align:middle;">
                <div style="font-size:14.5px;">{{ $item->product_name }}</div>
                <div style="font-size:12.5px;color:#a08a80;margin-top:2px;">
                    {{ collect([$item->variant_size ? 'Size '.$item->variant_size : null, $item->variant_color, 'Qty '.$item->quantity])->filter()->implode(' · ') }}
                </div>
            </td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #efe7e2;vertical-align:middle;font-size:14px;white-space:nowrap;">
                {{ $money($item->line_total) }}
            </td>
        </tr>
    @endforeach
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:12px;">
    <tr><td style="{{ $row }}">Subtotal</td><td align="right" style="{{ $row }}">{{ $money($order->subtotal) }}</td></tr>
    @if((float) $order->discount_total > 0)
        <tr><td style="{{ $row }}">Discount</td><td align="right" style="{{ $row }}color:#93524d;">−{{ $money($order->discount_total) }}</td></tr>
    @endif
    <tr>
        <td style="{{ $row }}">Shipping</td>
        <td align="right" style="{{ $row }}">{{ (float) $order->shipping_total > 0 ? $money($order->shipping_total) : 'Free' }}</td>
    </tr>
    <tr>
        <td style="padding-top:8px;font-size:17px;font-weight:bold;">Total</td>
        <td align="right" style="padding-top:8px;font-size:17px;font-weight:bold;">{{ $money($order->grand_total) }}</td>
    </tr>
</table>

<div style="margin-top:24px;padding-top:20px;border-top:1px solid #ded4cd;">
    <div style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:#a08a80;">Shipping to</div>
    <div style="margin-top:8px;font-size:14px;line-height:1.6;color:#6b5d57;">
        @foreach($order->addressLines() as $line)
            {{ $line }}<br>
        @endforeach
    </div>
</div>

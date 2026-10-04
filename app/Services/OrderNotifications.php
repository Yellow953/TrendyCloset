<?php

namespace App\Services;

use App\Mail\NewOrder;
use App\Mail\OrderReceived;
use App\Models\Order;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

use function Illuminate\Support\defer;

/**
 * Emails sent when an order is placed: the shop is told, and the shopper gets
 * a receipt if they gave an address. Sent after the response so a slow mail
 * server never holds up the thank-you page, and a failure only logs.
 */
class OrderNotifications
{
    public function placed(Order $order): void
    {
        $order->loadMissing('items.variant.product.images');

        $staff = self::recipients();

        if ($staff !== []) {
            defer(fn () => self::send($staff, new NewOrder($order), $order));
        }

        if ($order->email) {
            defer(fn () => self::send([$order->email], new OrderReceived($order), $order));
        }
    }

    /**
     * @return array<int, string>
     */
    public static function recipients(): array
    {
        return array_values(array_filter(array_map(
            'trim',
            explode(',', (string) config('store.notifications.orders')),
        )));
    }

    /**
     * @param  array<int, string>  $to
     */
    private static function send(array $to, Mailable $mail, Order $order): void
    {
        try {
            Mail::to($to)->send($mail);
        } catch (Throwable $e) {
            Log::warning('Order email failed', [
                'order' => $order->order_number,
                'mail' => class_basename($mail),
                'error' => $e->getMessage(),
            ]);
        }
    }
}

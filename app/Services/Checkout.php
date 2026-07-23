<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Support\Cart;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Turns a session bag into an order.
 *
 * There is no payment gateway: an order is written as {@see OrderStatus::Pending}
 * and someone in the back office moves it along. What this class guarantees is
 * that the order is a *snapshot* — every price, name, size and colour is copied
 * onto `orders` / `order_items` at the moment of purchase, so editing or
 * deleting the piece afterwards can never rewrite history (see the identity and
 * snapshot notes in CLAUDE.md).
 *
 * Stock is re-checked inside the transaction with the variant rows locked, so
 * two shoppers racing for the last garment cannot both win it.
 */
class Checkout
{
    public function __construct(private readonly Cart $cart) {}

    /**
     * @param  array{email: string, ship_name: string, ship_line1: string, ship_line2?: ?string, ship_city: string, ship_region?: ?string, ship_postcode?: ?string, ship_country: string, ship_phone?: ?string, notes?: ?string, marketing_opt_in?: bool}  $data
     *
     * @throws RuntimeException when the bag is empty or a line has since sold out
     */
    public function place(array $data): Order
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            throw new RuntimeException('Your bag is empty.');
        }

        $summary = $this->cart->summary();
        $coupon = $summary['coupon'];

        $order = DB::transaction(function () use ($lines, $summary, $coupon, $data) {
            // Lock the variants before reading stock, so the check below and
            // the decrement further down cannot be interleaved with another
            // checkout of the same garment.
            $locked = ProductVariant::whereIn('id', $lines->pluck('variant.id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lines as $line) {
                $variant = $locked->get($line['variant']->id);

                if (! $variant || $variant->stock < $line['qty']) {
                    throw new RuntimeException(
                        $line['variant']->product->name.' ('.$line['variant']->label.') just sold out.'
                    );
                }
            }

            $customer = Customer::forEmail($data['email'], [
                'name' => $data['ship_name'],
                'phone' => $data['ship_phone'] ?? null,
                'marketing_opt_in' => (bool) ($data['marketing_opt_in'] ?? false),
            ]);

            // A returning shopper keeps their CRM record but may have moved or
            // changed their mind about email — fill in what we did not know.
            $customer->fill(array_filter([
                'name' => $customer->name ?: $data['ship_name'],
                'phone' => $customer->phone ?: ($data['ship_phone'] ?? null),
            ]));

            if (! empty($data['marketing_opt_in'])) {
                $customer->marketing_opt_in = true;
            }

            $customer->save();

            $order = Order::create([
                'customer_id' => $customer->id,
                'coupon_id' => $coupon?->id,
                'order_number' => Order::nextNumber(),
                'status' => OrderStatus::Pending,
                'email' => mb_strtolower(trim($data['email'])),
                'ship_name' => $data['ship_name'],
                'ship_line1' => $data['ship_line1'],
                'ship_line2' => $data['ship_line2'] ?? null,
                'ship_city' => $data['ship_city'],
                'ship_region' => $data['ship_region'] ?? null,
                'ship_postcode' => $data['ship_postcode'] ?? null,
                'ship_country' => $data['ship_country'],
                'ship_phone' => $data['ship_phone'] ?? null,
                'subtotal' => $summary['subtotal'],
                'discount_total' => $summary['discount'],
                'shipping_total' => $summary['shipping'],
                'grand_total' => $summary['total'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $variant = $locked->get($line['variant']->id);
                $product = $line['variant']->product;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'variant_size' => $variant->size,
                    'variant_color' => $variant->color,
                    'sku' => $variant->sku,
                    'unit_price' => $line['unit'],
                    'quantity' => $line['qty'],
                    'line_total' => $line['total'],
                ]);

                $variant->decrement('stock', $line['qty']);
            }

            $coupon?->increment('used_count');

            return $order;
        });

        $this->cart->clear();

        return $order->load('items', 'customer');
    }

    /**
     * Put an order's garments back on the rail — used when the back office
     * cancels or refunds. Variants that have since been deleted are skipped.
     */
    public function restock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                ProductVariant::where('id', $item->product_variant_id)
                    ->increment('stock', $item->quantity);
            }
        });
    }

    /**
     * Take an order's garments back off the rail — the inverse of
     * {@see restock()}, for reviving a cancelled order.
     */
    public function destock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                ProductVariant::where('id', $item->product_variant_id)
                    ->decrement('stock', $item->quantity);
            }
        });
    }
}

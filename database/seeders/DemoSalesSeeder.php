<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\ProductEventType;
use App\Enums\SiteEventType;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductEvent;
use App\Models\ProductFavorite;
use App\Models\ProductVariant;
use App\Models\SiteEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Trading history for development. A fresh database has a catalogue but no
 * sales, so every card on the analytics page renders its empty state and there
 * is nothing to check the figures against.
 *
 * Deliberately NOT wired into DatabaseSeeder — this invents orders, and an
 * invented order in a real shop's books is worse than an empty chart. Run it by
 * name when you want something to look at:
 *
 *     php artisan db:seed --class=DemoSalesSeeder
 *
 * Idempotent: it does nothing at all if the database already holds orders.
 */
class DemoSalesSeeder extends Seeder
{
    private const DAYS = 120;

    private const ORDERS = 260;

    private const CUSTOMERS = 70;

    public function run(): void
    {
        $variants = ProductVariant::with('product')->where('is_active', true)->get();

        if ($variants->isEmpty()) {
            $this->command?->error('No variants to sell. Seed the catalogue first.');

            return;
        }

        // Each section guards itself, so adding a section later can be seeded
        // over a database that already has the earlier ones.
        $hasOrders = Order::exists();
        $hasTraffic = SiteEvent::exists();

        if ($hasOrders && $hasTraffic) {
            $this->command?->warn('Orders and traffic already exist — leaving them alone.');

            return;
        }

        $coupons = Coupon::where('is_active', true)->get();
        $products = $variants->pluck('product')->unique('id')->values();

        if ($hasOrders) {
            $this->command?->warn('Orders already exist — seeding traffic only.');
        } else {
            $customers = Customer::factory()->count(self::CUSTOMERS)->create();
            $this->orders($variants, $customers, $coupons);
            $this->engagement($products);
        }

        if (! $hasTraffic) {
            $this->traffic($products, $coupons);
        }

        $this->command?->info(sprintf(
            '%s orders · %s customers · %s product events · %s site events, over %d days.',
            number_format(Order::count()),
            number_format(Customer::count()),
            number_format(ProductEvent::count()),
            number_format(SiteEvent::count()),
            self::DAYS,
        ));
    }

    /**
     * Orders spread across the window, weighted towards recent weeks so the
     * period-on-period deltas have something to say. Roughly a third go to
     * someone who has ordered before, which is what makes the repeat-rate card
     * meaningful.
     *
     * @param  Collection<int, ProductVariant>  $variants
     * @param  Collection<int, Customer>  $customers
     * @param  Collection<int, Coupon>  $coupons
     */
    private function orders(Collection $variants, Collection $customers, Collection $coupons): void
    {
        // Cancelled and refunded are deliberately rare — they exist so the
        // revenue scope has something to exclude.
        $statuses = [
            ...array_fill(0, 5, OrderStatus::Completed),
            ...array_fill(0, 3, OrderStatus::Shipped),
            ...array_fill(0, 3, OrderStatus::Paid),
            ...array_fill(0, 2, OrderStatus::Processing),
            ...array_fill(0, 2, OrderStatus::Pending),
            OrderStatus::Cancelled,
            OrderStatus::Refunded,
        ];

        $seen = collect();
        $sequence = [];

        for ($i = 0; $i < self::ORDERS; $i++) {
            $placedAt = $this->weightedDate();

            // A returning buyer once there is anyone to return.
            $customer = $seen->isNotEmpty() && random_int(1, 100) <= 35
                ? $seen->random()
                : $customers->random();

            $seen->push($customer);

            $day = $placedAt->format('Ymd');
            $sequence[$day] = ($sequence[$day] ?? 0) + 1;

            $order = Order::factory()->create([
                'customer_id' => $customer->id,
                'order_number' => 'TC-'.$day.'-'.str_pad((string) $sequence[$day], 4, '0', STR_PAD_LEFT),
                'status' => $statuses[array_rand($statuses)],
                'ship_name' => $customer->name,
                'ship_phone' => $customer->phone,
                'email' => $customer->email,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);

            $this->lines($order, $variants, $placedAt);
            $this->settle($order, $coupons);
        }
    }

    /**
     * @param  Collection<int, ProductVariant>  $variants
     */
    private function lines(Order $order, Collection $variants, Carbon $placedAt): void
    {
        foreach ($variants->random(random_int(1, 3)) as $variant) {
            $quantity = random_int(1, 2);
            $price = (float) $variant->effective_price;

            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product->name,
                'variant_size' => $variant->size,
                'variant_color' => $variant->color,
                'sku' => $variant->sku,
                'unit_price' => $price,
                'quantity' => $quantity,
                'line_total' => round($price * $quantity, 2),
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);
        }
    }

    /**
     * Total the lines the way checkout would, so the money on the order agrees
     * with the money on its lines.
     *
     * @param  Collection<int, Coupon>  $coupons
     */
    private function settle(Order $order, Collection $coupons): void
    {
        $subtotal = (float) $order->items()->sum('line_total');

        $coupon = $coupons->isNotEmpty() && random_int(1, 100) <= 20
            ? $coupons->random()
            : null;

        $discount = $coupon?->discountFor($subtotal) ?? 0.0;
        $shipping = ($subtotal - $discount) >= \App\Support\Cart::FREE_SHIPPING_THRESHOLD
            ? 0.0
            : \App\Support\Cart::STANDARD_SHIPPING;

        $order->forceFill([
            'coupon_id' => $coupon?->id,
            'subtotal' => $subtotal,
            'discount_total' => $discount,
            'shipping_total' => $shipping,
            'grand_total' => round($subtotal - $discount + $shipping, 2),
        ])->save();
    }

    /**
     * Views, adds to bag and a scatter of favourites. Views outnumber adds by
     * roughly the margin a real shop sees, so the funnel card reads plausibly.
     *
     * @param  Collection<int, Product>  $products
     */
    private function engagement(Collection $products): void
    {
        $rows = [];
        $visitors = collect(range(1, 400))->map(fn () => (string) Str::uuid());

        foreach ($products as $product) {
            $views = random_int(20, 220);

            for ($i = 0; $i < $views; $i++) {
                $at = $this->weightedDate();

                $rows[] = [
                    'product_id' => $product->id,
                    'type' => ProductEventType::View->value,
                    'visitor_id' => $visitors->random(),
                    'created_at' => $at,
                ];

                if (random_int(1, 100) <= 12) {
                    $rows[] = [
                        'product_id' => $product->id,
                        'type' => ProductEventType::AddToCart->value,
                        'visitor_id' => $visitors->random(),
                        'created_at' => $at,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            ProductEvent::insert($chunk);
        }

        foreach ($products->random(min(18, $products->count())) as $product) {
            foreach ($visitors->random(random_int(1, 9)) as $visitor) {
                ProductFavorite::firstOrCreate([
                    'product_id' => $product->id,
                    'visitor_id' => $visitor,
                ], ['created_at' => $this->weightedDate()]);
            }
        }
    }

    /**
     * Site-wide traffic: page views across the real routes, arriving from a
     * plausible spread of channels, plus the named events. Each visit is a run
     * of a few pages under one session id, so "pages per visit" and the
     * top-pages list mean something rather than being one row per visitor.
     *
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Coupon>  $coupons
     */
    private function traffic(Collection $products, Collection $coupons): void
    {
        $referrers = [
            null, null, null, null,                      // direct, the biggest slice
            'https://www.instagram.com/', 'https://l.instagram.com/',
            'https://www.google.com/search', 'https://www.google.com/',
            'https://www.facebook.com/', 'https://www.tiktok.com/',
            'https://wa.me/', 'https://www.pinterest.com/',
        ];

        $landing = ['/', '/', '/', '/shop', '/shop', '/about', '/contact'];
        $slugs = $products->pluck('slug')->all();
        $terms = ['linen', 'jeans', 'mom jeans', 'summer dress', 'coat', 'wide leg', 'black shirt', 'sequin'];

        $rows = [];
        $visits = 900;

        for ($v = 0; $v < $visits; $v++) {
            $visitor = (string) Str::uuid();
            $session = mb_substr(hash('sha256', $visitor.$v), 0, 32);
            $at = $this->weightedDate();
            $referrer = $referrers[array_rand($referrers)];
            $host = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;

            $path = $landing[array_rand($landing)];
            $depth = random_int(1, 7);

            for ($p = 0; $p < $depth; $p++) {
                $at = $at->copy()->addSeconds(random_int(15, 240));

                $rows[] = [
                    'name' => SiteEventType::PageView->value,
                    'visitor_id' => $visitor,
                    'session_id' => $session,
                    'path' => $path,
                    // Only the first page of a visit carries the outside
                    // referrer; after that the shopper is walking our own site.
                    'referrer_host' => $p === 0 ? $host : null,
                    'referrer' => $p === 0 ? $referrer : null,
                    'meta' => null,
                    'created_at' => $at,
                ];

                // Where they go next.
                $path = match (random_int(1, 10)) {
                    1, 2, 3, 4 => '/product/'.$slugs[array_rand($slugs)],
                    5, 6 => '/shop',
                    7 => '/bag',
                    8 => '/favorites',
                    9 => '/policies/shipping',
                    default => '/',
                };
            }

            // Named events, hung off the same visit.
            $event = function (SiteEventType $type, array $meta = []) use (&$rows, $visitor, $session, &$at, $path) {
                $rows[] = [
                    'name' => $type->value,
                    'visitor_id' => $visitor,
                    'session_id' => $session,
                    'path' => $path,
                    'referrer_host' => null,
                    'referrer' => null,
                    'meta' => $meta ? json_encode($meta) : null,
                    'created_at' => $at->copy()->addSeconds(random_int(5, 90)),
                ];
            };

            if (random_int(1, 100) <= 22) {
                $term = $terms[array_rand($terms)];
                $event(SiteEventType::Search, ['q' => $term, 'results' => random_int(0, 8)]);
            }

            if (random_int(1, 100) <= 14) {
                $event(SiteEventType::CheckoutStarted, ['value' => random_int(60, 320)]);

                // Most, but not all, of those go on to order.
                if (random_int(1, 100) <= 62) {
                    $event(SiteEventType::OrderPlaced, ['value' => random_int(60, 320)]);
                }
            }

            if ($coupons->isNotEmpty() && random_int(1, 100) <= 6) {
                $event(SiteEventType::CouponApplied, ['code' => $coupons->random()->code]);
            }

            if (random_int(1, 100) <= 9) {
                $event(SiteEventType::WhatsappClick, ['from' => $path]);
            }

            if (random_int(1, 100) <= 4) {
                $event(SiteEventType::ContactFormSent);
            }

            if (random_int(1, 100) <= 7) {
                $event(SiteEventType::FavouriteAdded);
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            SiteEvent::insert($chunk);
        }
    }

    /**
     * A date in the window, biased towards the recent end — squaring a 0–1 roll
     * pulls it towards zero, i.e. towards today.
     */
    private function weightedDate(): Carbon
    {
        $roll = random_int(0, 1000) / 1000;

        $at = now()
            ->subDays((int) round($roll ** 2 * self::DAYS))
            ->setTime(random_int(8, 22), random_int(0, 59));

        // Shop hours run to 22:00, so a roll that lands on today can easily
        // pick an hour that has not happened yet — and an order dated "7h from
        // now" makes the whole screen look broken.
        return $at->isFuture()
            ? now()->subMinutes(random_int(5, 240))
            : $at;
    }
}

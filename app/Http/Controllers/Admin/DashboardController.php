<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\ProductEventType;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductEvent;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The back office landing screen: what happened, what needs doing, what is
 * running out. Every figure is derived — nothing here is stored.
 */
class DashboardController extends Controller
{
    /** A garment at or below this stock level is worth flagging. */
    private const LOW_STOCK = 3;

    public function index()
    {
        $since = now()->subDays(29)->startOfDay();
        $previous = now()->subDays(59)->startOfDay();

        return view('admin.dashboard', [
            'active' => 'dashboard',
            'kpis' => $this->kpis($since, $previous),
            'revenueSeries' => $this->revenueSeries(),
            'statusCounts' => $this->statusCounts(),
            'recentOrders' => Order::with('customer')->latest()->limit(8)->get(),
            'topProducts' => $this->topProducts($since),
            'lowStock' => $this->lowStock(),
            'unreadMessages' => ContactMessage::whereNull('read_at')->latest()->limit(5)->get(),
        ]);
    }

    /**
     * The four headline figures, each with its change against the preceding
     * window of the same length — a number without a direction is not news.
     *
     * @return array<int, array{label: string, value: string, delta: ?float, hint: string}>
     */
    private function kpis(Carbon $since, Carbon $previous): array
    {
        $revenue = (float) Order::revenue()->where('created_at', '>=', $since)->sum('grand_total');
        $revenuePrior = (float) Order::revenue()->whereBetween('created_at', [$previous, $since])->sum('grand_total');

        $orders = Order::where('created_at', '>=', $since)->count();
        $ordersPrior = Order::whereBetween('created_at', [$previous, $since])->count();

        $customers = Customer::where('created_at', '>=', $since)->count();
        $customersPrior = Customer::whereBetween('created_at', [$previous, $since])->count();

        $views = ProductEvent::where('type', ProductEventType::View)->where('created_at', '>=', $since)->count();
        $viewsPrior = ProductEvent::where('type', ProductEventType::View)->whereBetween('created_at', [$previous, $since])->count();

        return [
            [
                'label' => 'Revenue',
                'value' => Product::money($revenue),
                'delta' => $this->delta($revenue, $revenuePrior),
                'hint' => 'Last 30 days, excluding cancelled and refunded',
            ],
            [
                'label' => 'Orders',
                'value' => number_format($orders),
                'delta' => $this->delta($orders, $ordersPrior),
                'hint' => $orders > 0 ? Product::money($revenue / max($orders, 1)).' average basket' : 'No orders yet',
            ],
            [
                'label' => 'New customers',
                'value' => number_format($customers),
                'delta' => $this->delta($customers, $customersPrior),
                'hint' => number_format(Customer::count()).' on the books',
            ],
            [
                'label' => 'Product views',
                'value' => number_format($views),
                'delta' => $this->delta($views, $viewsPrior),
                'hint' => 'Deduplicated per visitor',
            ],
        ];
    }

    /**
     * Percentage change, or null when there is no baseline to compare against
     * (showing "+100%" against zero would be theatre, not information).
     */
    private function delta(float $now, float $prior): ?float
    {
        if ($prior <= 0) {
            return null;
        }

        return round((($now - $prior) / $prior) * 100, 1);
    }

    /**
     * Revenue per day for the last 14 days, zero-filled so the chart has a bar
     * for every day rather than skipping the quiet ones.
     *
     * @return Collection<int, array{label: string, day: string, value: float}>
     */
    private function revenueSeries(): Collection
    {
        $start = now()->subDays(13)->startOfDay();

        $totals = Order::revenue()
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'grand_total'])
            ->groupBy(fn (Order $o) => $o->created_at->toDateString())
            ->map(fn (Collection $group) => (float) $group->sum('grand_total'));

        return collect(range(0, 13))->map(function (int $offset) use ($start, $totals) {
            $date = $start->copy()->addDays($offset);

            return [
                'label' => $date->format('j M'),
                'day' => $date->format('D'),
                'value' => (float) ($totals[$date->toDateString()] ?? 0),
            ];
        });
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        $counts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $s) => [$s->value => (int) ($counts[$s->value] ?? 0)])
            ->all();
    }

    /**
     * Best sellers by units actually sold, falling back to engagement when
     * nothing has sold yet — a new shop still wants to know what is looked at.
     *
     * @return Collection<int, Product>
     */
    private function topProducts(Carbon $since): Collection
    {
        return Product::query()
            ->select('products.*')
            ->with('category')
            ->withEngagement($since)
            ->addSelect(['units_sold' => OrderItem::query()
                ->selectRaw('COALESCE(SUM(order_items.quantity), 0)')
                ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->whereColumn('product_variants.product_id', 'products.id'),
            ])
            ->orderByDesc('units_sold')
            ->orderByDesc('views_count')
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    private function lowStock(): Collection
    {
        return ProductVariant::with('product')
            ->where('is_active', true)
            ->where('stock', '<=', self::LOW_STOCK)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->orderBy('stock')
            ->limit(8)
            ->get();
    }
}

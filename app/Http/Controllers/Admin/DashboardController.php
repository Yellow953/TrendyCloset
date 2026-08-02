<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductEventType;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductEvent;
use App\Models\ProductVariant;
use App\Models\SiteEvent;
use App\Support\DateRange;
use App\Support\Stats;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The back office landing screen: how the shop is trading right now, and what
 * needs doing. Deliberately short — anything that invites a question rather
 * than an action belongs on Analytics, which has the date range to answer it.
 *
 * Every figure is derived; nothing here is stored.
 */
class DashboardController extends Controller
{
    /** A garment at or below this stock level is worth flagging. */
    public const LOW_STOCK = 3;

    /**
     * The periods the trend chart offers, longest first as the default.
     *
     * @var array<string, array{label: string, days: int}>
     */
    public const TRENDS = [
        '30d' => ['label' => 'Last 30 days', 'days' => 30],
        '7d' => ['label' => 'Last 7 days', 'days' => 7],
        'today' => ['label' => 'Today', 'days' => 1],
    ];

    public function index(Request $request)
    {
        $since = now()->subDays(29)->startOfDay();
        $previous = now()->subDays(59)->startOfDay();

        $trend = $request->input('trend');
        $trend = array_key_exists($trend, self::TRENDS) ? $trend : array_key_first(self::TRENDS);

        return view('admin.dashboard', [
            'active' => 'dashboard',
            'kpis' => $this->kpis($since, $previous),
            'trend' => $trend,
            'funnel' => $this->funnel(DateRange::lastDays(self::TRENDS[$trend]['days'])),
            'recentOrders' => Order::with('customer')->latest()->limit(7)->get(),
            'lowStock' => $this->lowStock(),
            'unreadMessages' => ContactMessage::whereNull('read_at')->latest()->limit(5)->get(),
        ]);
    }

    /**
     * Four figures, each against the preceding window of the same length — a
     * number without a direction is not news.
     *
     * @return array<int, array{label: string, value: string, delta: ?float, hint: string}>
     */
    private function kpis(Carbon $since, Carbon $previous): array
    {
        $revenue = (float) Order::revenue()->where('created_at', '>=', $since)->sum('grand_total');
        $revenuePrior = (float) Order::revenue()->whereBetween('created_at', [$previous, $since])->sum('grand_total');

        $orders = Order::where('created_at', '>=', $since)->count();
        $ordersPrior = Order::whereBetween('created_at', [$previous, $since])->count();

        // Sittings, not people — the same unit Analytics calls "visits".
        $visits = SiteEvent::where('created_at', '>=', $since)->distinct()->count('session_id');
        $visitsPrior = SiteEvent::whereBetween('created_at', [$previous, $since])->distinct()->count('session_id');

        $rate = $visits > 0 ? Stats::share($orders, $visits) : 0.0;
        $ratePrior = $visitsPrior > 0 ? Stats::share($ordersPrior, $visitsPrior) : 0.0;

        return [
            [
                'label' => 'Revenue',
                'value' => Product::money($revenue),
                'delta' => Stats::delta($revenue, $revenuePrior),
                'hint' => 'Excluding cancelled and refunded',
            ],
            [
                'label' => 'Orders',
                'value' => number_format($orders),
                'delta' => Stats::delta($orders, $ordersPrior),
                'hint' => $orders > 0 ? Product::money($revenue / $orders).' average basket' : 'No orders yet',
            ],
            [
                'label' => 'Visits',
                'value' => number_format($visits),
                'delta' => Stats::delta($visits, $visitsPrior),
                'hint' => 'Sessions on the storefront',
            ],
            [
                'label' => 'Conversion',
                'value' => $visits > 0 ? $rate.'%' : '—',
                'delta' => Stats::delta($rate, $ratePrior),
                'hint' => 'Visits that ended in an order',
            ],
        ];
    }

    /**
     * Views → added to bag → orders, as three lines on one shared scale over
     * the chosen period. One scale, never two: a second y-axis lets any pair of
     * lines be made to cross wherever you like. Views dwarfing the other two is
     * the true shape of a shop, and the legend totals plus the rates underneath
     * carry the exact numbers the lower lines cannot show.
     *
     * @return array{lines: array<int, array<string, mixed>>, buckets: Collection<int, array<string, mixed>>, steps: array<int, array<string, mixed>>}
     */
    private function funnel(DateRange $range): array
    {
        $views = Stats::series(ProductEvent::query()->where('type', ProductEventType::View), $range);
        $bags = Stats::series(ProductEvent::query()->where('type', ProductEventType::AddToCart), $range);
        $orders = Stats::series(Order::revenue(), $range);

        $totals = [
            'views' => (int) $views->sum('value'),
            'bags' => (int) $bags->sum('value'),
            'orders' => (int) $orders->sum('value'),
        ];

        // Categorical slots 1–3 of the validated palette, in fixed order. The
        // hue belongs to the step, not to its rank, so it never moves when the
        // period changes.
        $lines = [
            ['label' => 'Product views', 'color' => '#2a78d6', 'values' => $views->pluck('value')->all(), 'total' => $totals['views']],
            ['label' => 'Added to bag', 'color' => '#eb6834', 'values' => $bags->pluck('value')->all(), 'total' => $totals['bags']],
            ['label' => 'Orders', 'color' => '#1baf7a', 'values' => $orders->pluck('value')->all(), 'total' => $totals['orders']],
        ];

        return [
            'lines' => $lines,
            'buckets' => $views,
            'steps' => [
                [
                    'label' => 'View → bag',
                    'rate' => $totals['views'] > 0 ? Stats::share($totals['bags'], $totals['views']) : null,
                    'hint' => number_format($totals['bags']).' of '.number_format($totals['views']),
                ],
                [
                    'label' => 'Bag → order',
                    'rate' => $totals['bags'] > 0 ? Stats::share($totals['orders'], $totals['bags']) : null,
                    'hint' => number_format($totals['orders']).' of '.number_format($totals['bags']),
                ],
            ],
        ];
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
            ->limit(6)
            ->get();
    }
}

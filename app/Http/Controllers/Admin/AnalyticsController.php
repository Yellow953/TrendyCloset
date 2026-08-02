<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\ProductEventType;
use App\Enums\SiteEventType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductEvent;
use App\Models\ProductVariant;
use App\Models\SiteEvent;
use App\Support\DateRange;
use App\Support\Stats;
use App\Support\TrafficSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The reporting screen. Where the dashboard answers "what needs doing today",
 * this answers "how is the shop trading" over a window the person chooses —
 * what sold, what was looked at and not bought, who came back, what is dead on
 * the rail.
 *
 * Everything is derived on the fly from orders, events and stock. Nothing is
 * stored, so there is no aggregate to fall out of date and no job to run.
 *
 * Two caveats the cards state on the page, because they change how the numbers
 * should be read: views are deduplicated per visitor for 30 minutes, and
 * favourites are current state rather than events, so they cannot be windowed.
 */
class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $range = DateRange::fromRequest($request);

        return view('admin.analytics', [
            'active' => 'analytics',
            'range' => $range,
            'sort' => $this->sort($request),
            'kpis' => $this->kpis($range),
            'traffic' => $this->traffic($range),
            'trafficSeries' => Stats::series(SiteEvent::query()->pageViews(), $range),
            'topPages' => $this->topPages($range),
            'sources' => $this->sources($range),
            'customEvents' => $this->customEvents($range),
            'searches' => $this->searches($range),
            'revenueSeries' => Stats::series(Order::revenue(), $range, 'grand_total'),
            'takings' => $this->takings($range),
            'categoryRevenue' => $this->categoryRevenue($range),
            'statusMix' => $this->statusMix($range),
            'coupons' => $this->coupons($range),
            'funnel' => $this->funnel($range),
            'products' => $this->products($request, $range),
            'unsold' => $this->unsold($range),
            'mostSaved' => $this->mostSaved(),
            'customers' => $this->customers($range),
            'customerSeries' => Stats::series(Customer::query(), $range),
            'topCustomers' => $this->topCustomers($range),
            'inventory' => $this->inventory($range),
            'deadStock' => $this->deadStock(),
            'stockByCategory' => $this->stockByCategory(),
        ]);
    }

    // -----------------------------------------------------------------------
    // Headline
    // -----------------------------------------------------------------------

    /**
     * Six figures, each against the same window immediately before it. "All
     * time" has nothing behind it, so those deltas come back null and simply
     * do not render.
     *
     * @return array<int, array{label: string, value: string, delta: ?float, hint: string}>
     */
    private function kpis(DateRange $range): array
    {
        $prior = $range->previous();

        $revenue = $this->revenue($range);
        $orders = $this->orderCount($range);
        $customers = $this->newCustomers($range);
        $views = $this->events($range, ProductEventType::View);
        $adds = $this->events($range, ProductEventType::AddToCart);

        $basket = $orders > 0 ? $revenue / $orders : 0.0;
        $bagRate = $views > 0 ? round(($adds / $views) * 100, 1) : 0.0;

        $priorOrders = $prior ? $this->orderCount($prior) : 0;
        $priorViews = $prior ? $this->events($prior, ProductEventType::View) : 0;
        $priorAdds = $prior ? $this->events($prior, ProductEventType::AddToCart) : 0;

        return [
            [
                'label' => 'Revenue',
                'value' => Product::money($revenue),
                'delta' => $prior ? Stats::delta($revenue, $this->revenue($prior)) : null,
                'hint' => 'Excluding cancelled and refunded',
            ],
            [
                'label' => 'Orders',
                'value' => number_format($orders),
                'delta' => $prior ? Stats::delta($orders, $priorOrders) : null,
                'hint' => 'Every order placed in the period',
            ],
            [
                'label' => 'Average basket',
                'value' => Product::money($basket),
                'delta' => $prior && $priorOrders > 0
                    ? Stats::delta($basket, $this->revenue($prior) / $priorOrders)
                    : null,
                'hint' => 'Revenue over paid orders',
            ],
            [
                'label' => 'New customers',
                'value' => number_format($customers),
                'delta' => $prior ? Stats::delta($customers, $this->newCustomers($prior)) : null,
                'hint' => number_format(Customer::count()).' on the books',
            ],
            [
                'label' => 'Product views',
                'value' => number_format($views),
                'delta' => $prior ? Stats::delta($views, $priorViews) : null,
                'hint' => 'Deduplicated per visitor, 30 minutes',
            ],
            [
                'label' => 'View → bag',
                'value' => $views > 0 ? $bagRate.'%' : '—',
                'delta' => $prior && $priorViews > 0
                    ? Stats::delta($bagRate, round(($priorAdds / $priorViews) * 100, 1))
                    : null,
                'hint' => number_format($adds).' added to a bag',
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Traffic
    // -----------------------------------------------------------------------

    /**
     * The four figures a shopkeeper checks first. "Visits" counts sittings —
     * one session, expiring the way a visit does — while "visitors" counts
     * people, off the forever cookie, so the same shopper returning next week
     * is one visitor and two visits.
     *
     * @return array<int, array{label: string, value: string, delta: ?float, hint: string}>
     */
    private function traffic(DateRange $range): array
    {
        $prior = $range->previous();

        $views = $this->pageViews($range);
        $visits = $this->visits($range);
        $visitors = $this->visitors($range);
        $orders = $this->orderCount($range);

        $perVisit = $visits > 0 ? round($views / $visits, 1) : 0.0;
        $priorVisits = $prior ? $this->visits($prior) : 0;

        return [
            [
                'label' => 'Page views',
                'value' => number_format($views),
                'delta' => $prior ? Stats::delta($views, $this->pageViews($prior)) : null,
                'hint' => 'Every storefront page opened',
            ],
            [
                'label' => 'Visits',
                'value' => number_format($visits),
                'delta' => $prior ? Stats::delta($visits, $priorVisits) : null,
                'hint' => 'Sessions — one sitting each',
            ],
            [
                'label' => 'Visitors',
                'value' => number_format($visitors),
                'delta' => $prior ? Stats::delta($visitors, $this->visitors($prior)) : null,
                'hint' => 'People, counted by the forever cookie',
            ],
            [
                'label' => 'Pages per visit',
                'value' => $visits > 0 ? (string) $perVisit : '—',
                'delta' => null,
                'hint' => 'How far people get into the shop',
            ],
            [
                'label' => 'Conversion',
                'value' => $visits > 0 ? Stats::share($orders, $visits).'%' : '—',
                'delta' => null,
                'hint' => number_format($orders).' orders from '.number_format($visits).' visits',
            ],
        ];
    }

    /**
     * The pages people actually open, with how many distinct visits reached
     * each — a page opened forty times by one person is not a popular page.
     *
     * @return Collection<int, array{path: string, views: int, visits: int}>
     */
    private function topPages(DateRange $range): Collection
    {
        return $range->apply(SiteEvent::query()->pageViews())
            ->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT session_id) as visits')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(12)
            ->get()
            ->map(fn (SiteEvent $row) => [
                'path' => $row->path,
                'views' => (int) $row->views,
                'visits' => (int) $row->visits,
            ]);
    }

    /**
     * Where visits come from, rolled up to channels — "Instagram" rather than
     * four different instagram hostnames. Only the first page of a visit
     * carries a referrer, so this counts arrivals, not pages.
     *
     * @return Collection<int, array{label: string, visits: int}>
     */
    private function sources(DateRange $range): Collection
    {
        $rows = $range->apply(SiteEvent::query()->pageViews())
            ->selectRaw('referrer_host, COUNT(DISTINCT session_id) as visits')
            ->groupBy('referrer_host')
            ->get();

        $totals = [];

        foreach ($rows as $row) {
            $label = TrafficSource::label($row->referrer_host);

            $totals[$label] ??= ['label' => $label, 'visits' => 0];
            $totals[$label]['visits'] += (int) $row->visits;
        }

        return collect($totals)->sortByDesc('visits')->values();
    }

    /**
     * Every named event with its count and how many distinct people fired it.
     * Page views are excluded — they have four cards of their own.
     *
     * @return Collection<int, array{type: SiteEventType, total: int, visitors: int}>
     */
    private function customEvents(DateRange $range): Collection
    {
        $rows = $range->apply(SiteEvent::query())
            ->where('name', '!=', SiteEventType::PageView)
            ->selectRaw('name, COUNT(*) as total, COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('name')
            ->get()
            ->keyBy(fn (SiteEvent $row) => $row->name->value);

        // Driven off the enum rather than the rows, so an event that has never
        // fired still shows as zero instead of silently not existing.
        return collect(SiteEventType::custom())
            ->map(fn (SiteEventType $type) => [
                'type' => $type,
                'total' => (int) ($rows[$type->value]->total ?? 0),
                'visitors' => (int) ($rows[$type->value]->visitors ?? 0),
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * What people typed into search, and how many results it found. A term
     * that keeps coming back with nothing is the catalogue telling you what to
     * buy next, so the empty ones are kept rather than filtered out.
     *
     * @return Collection<int, array{term: string, searches: int, results: int}>
     */
    private function searches(DateRange $range): Collection
    {
        return $range->apply(SiteEvent::query())
            ->ofType(SiteEventType::Search)
            ->get(['meta'])
            ->groupBy(fn (SiteEvent $e) => mb_strtolower(trim((string) ($e->meta['q'] ?? ''))))
            ->reject(fn (Collection $group, string $term) => $term === '')
            ->map(fn (Collection $group, string $term) => [
                'term' => $term,
                'searches' => $group->count(),
                // The most recent count, since the catalogue changes under it.
                'results' => (int) ($group->last()->meta['results'] ?? 0),
            ])
            ->sortByDesc('searches')
            ->take(12)
            ->values();
    }

    private function pageViews(DateRange $range): int
    {
        return $range->apply(SiteEvent::query()->pageViews())->count();
    }

    private function visits(DateRange $range): int
    {
        return $range->apply(SiteEvent::query())->distinct()->count('session_id');
    }

    private function visitors(DateRange $range): int
    {
        return $range->apply(SiteEvent::query())->distinct()->count('visitor_id');
    }

    // -----------------------------------------------------------------------
    // Sales
    // -----------------------------------------------------------------------

    /**
     * Where the money came from — the four lines that make up a total, so a
     * quiet month with heavy discounting reads differently from a quiet month.
     *
     * @return array<int, array{label: string, value: string, note: string}>
     */
    private function takings(DateRange $range): array
    {
        $totals = $range->apply(Order::revenue())
            ->selectRaw('COALESCE(SUM(subtotal), 0) as subtotal')
            ->selectRaw('COALESCE(SUM(discount_total), 0) as discount')
            ->selectRaw('COALESCE(SUM(shipping_total), 0) as shipping')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as grand')
            ->first();

        $subtotal = (float) $totals->subtotal;
        $discount = (float) $totals->discount;

        return [
            ['label' => 'Garments', 'value' => Product::money($subtotal), 'note' => 'Before discount and delivery'],
            ['label' => 'Discounts given', 'value' => '−'.Product::money($discount), 'note' => $subtotal > 0 ? Stats::share($discount, $subtotal).'% of garment value' : 'Nothing discounted'],
            ['label' => 'Delivery collected', 'value' => Product::money((float) $totals->shipping), 'note' => 'Charged at checkout'],
            ['label' => 'Taken', 'value' => Product::money((float) $totals->grand), 'note' => 'What the orders are worth'],
        ];
    }

    /**
     * Revenue by root category. Products are filed on leaves, so a section's
     * figure is the sum of everything beneath it.
     *
     * The join runs order_items → product_variants → products, because an order
     * line snapshots the garment rather than pointing at it and there is no
     * product_id to shortcut through. A line whose variant has since been
     * deleted has nowhere to land — those are grouped as "Unattributed" rather
     * than quietly dropped, so the parts still add up to the whole.
     *
     * @return Collection<int, array{label: string, revenue: float, units: int}>
     */
    private function categoryRevenue(DateRange $range): Collection
    {
        $roots = $this->rootMap();

        $rows = $this->soldLines($range)
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('products.category_id as category_id')
            ->selectRaw('COALESCE(SUM(order_items.line_total), 0) as revenue')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as units')
            ->groupBy('products.category_id')
            ->get();

        $totals = [];

        foreach ($rows as $row) {
            $label = $roots[$row->category_id] ?? 'Unattributed';

            $totals[$label] ??= ['label' => $label, 'revenue' => 0.0, 'units' => 0];
            $totals[$label]['revenue'] += (float) $row->revenue;
            $totals[$label]['units'] += (int) $row->units;
        }

        return collect($totals)->sortByDesc('revenue')->values();
    }

    /**
     * @return array<string, int>
     */
    private function statusMix(DateRange $range): array
    {
        $counts = $range->apply(Order::query())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $s) => [$s->value => (int) ($counts[$s->value] ?? 0)])
            ->all();
    }

    /**
     * @return Collection<int, Coupon>
     */
    private function coupons(DateRange $range): Collection
    {
        $window = fn (Builder $q) => $range->apply($q->revenue(), 'orders.created_at');

        return Coupon::query()
            ->withCount(['orders as uses' => $window])
            ->withSum(['orders as discount_given' => $window], 'discount_total')
            ->withSum(['orders as revenue_taken' => $window], 'grand_total')
            ->get()
            ->filter(fn (Coupon $c) => $c->uses > 0)
            ->sortByDesc('revenue_taken')
            ->values();
    }

    // -----------------------------------------------------------------------
    // Product engagement
    // -----------------------------------------------------------------------

    /**
     * Views → bag → sold. A population funnel rather than a per-visitor one:
     * events carry a visitor id but an order does not, so these are three
     * counts of the same period, not one cohort followed through.
     *
     * Each rate therefore names its own denominator rather than saying "of the
     * step above" — these are ratios between independent counts, and one can
     * legitimately exceed 100% (a Buy Now never fires an add-to-bag event).
     *
     * @return array<int, array{label: string, value: int, note: ?string}>
     */
    private function funnel(DateRange $range): array
    {
        $views = $this->events($range, ProductEventType::View);
        $adds = $this->events($range, ProductEventType::AddToCart);
        $sold = $this->unitsSold($range);

        return [
            ['label' => 'Viewed', 'value' => $views, 'note' => 'Product pages opened'],
            [
                'label' => 'Added to a bag',
                'value' => $adds,
                'note' => $views > 0 ? Stats::share($adds, $views).'% of views' : null,
            ],
            [
                'label' => 'Bought',
                'value' => $sold,
                'note' => $adds > 0 ? Stats::share($sold, $adds).'% of bag adds' : null,
            ],
        ];
    }

    /**
     * The catalogue ranked by whichever column the person clicked. Units sold
     * is scoped to the window *and* to statuses that count as revenue — a
     * cancelled order is not a sale, which is what the dashboard's older
     * version of this query gets wrong.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<Product>
     */
    private function products(Request $request, DateRange $range)
    {
        $sort = $this->sort($request);

        $column = match ($sort) {
            'bag' => 'add_to_cart_count',
            'saved' => 'favorites_count',
            'sold' => 'units_sold',
            default => 'views_count',
        };

        return $this->rankedProducts($range)
            ->orderByDesc($column)
            ->orderBy('products.name')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Traffic that never converted — the merchandising signal the shop cannot
     * get from the orders table alone. Ordered by views, so the top of the list
     * is the piece costing the most attention for nothing.
     *
     * @return Collection<int, Product>
     */
    private function unsold(DateRange $range): Collection
    {
        return $this->rankedProducts($range)
            ->orderByDesc('views_count')
            ->limit(40)
            ->get()
            ->filter(fn (Product $p) => (int) $p->units_sold === 0 && $p->views_count > 0)
            ->take(8)
            ->values();
    }

    /**
     * Favourites are state, not events — unfavouriting deletes the row — so
     * this is always all-time, whatever window the page is showing.
     *
     * @return Collection<int, Product>
     */
    private function mostSaved(): Collection
    {
        return Product::query()
            ->with('category')
            ->withCount('favorites as favorites_count')
            ->whereHas('favorites')
            ->orderByDesc('favorites_count')
            ->limit(8)
            ->get();
    }

    // -----------------------------------------------------------------------
    // Customers
    // -----------------------------------------------------------------------

    /**
     * @return array<int, array{label: string, value: string, note: string}>
     */
    private function customers(DateRange $range): array
    {
        $orders = $this->orderCount($range);

        // An order is "returning" when that customer had already ordered before
        // this one was placed — judged per order, not per customer, so the
        // split adds up to the orders in the window.
        $returning = $range->apply(Order::revenue())
            ->whereNotNull('customer_id')
            ->whereExists(fn ($q) => $q->from('orders as prior')
                ->whereColumn('prior.customer_id', 'orders.customer_id')
                ->whereColumn('prior.created_at', '<', 'orders.created_at'))
            ->count();

        $buyers = $range->apply(Order::revenue())->distinct()->count('customer_id');

        return [
            [
                'label' => 'Buyers',
                'value' => number_format($buyers),
                'note' => 'Distinct customers who ordered',
            ],
            [
                'label' => 'Repeat orders',
                'value' => number_format($returning),
                'note' => $orders > 0 ? Stats::share($returning, $orders).'% of orders in the period' : 'No orders yet',
            ],
            [
                'label' => 'Orders per buyer',
                'value' => $buyers > 0 ? number_format($orders / $buyers, 2) : '—',
                'note' => 'Across the period',
            ],
            [
                'label' => 'New customers',
                'value' => number_format($this->newCustomers($range)),
                'note' => 'First seen in the period',
            ],
        ];
    }

    /**
     * @return Collection<int, Customer>
     */
    private function topCustomers(DateRange $range): Collection
    {
        $window = fn (Builder $q) => $range->apply($q->revenue(), 'orders.created_at');

        return Customer::query()
            ->withCount(['orders as order_count' => $window])
            ->withSum(['orders as spend' => $window], 'grand_total')
            ->whereHas('orders', $window)
            ->orderByDesc('spend')
            ->limit(8)
            ->get();
    }

    // -----------------------------------------------------------------------
    // Inventory
    // -----------------------------------------------------------------------

    /**
     * @return array<int, array{label: string, value: string, note: string}>
     */
    private function inventory(DateRange $range): array
    {
        // Columns stay qualified: the stock-value figure joins products, and
        // both tables carry `is_active` and `stock`-adjacent names.
        $active = ProductVariant::query()
            ->where('product_variants.is_active', true)
            ->whereHas('product', fn ($q) => $q->where('products.is_active', true));

        $onHand = (int) (clone $active)->sum('product_variants.stock');

        $value = (float) (clone $active)
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->sum(DB::raw('product_variants.stock * COALESCE(product_variants.price_override, products.price)'));

        $out = (clone $active)->where('product_variants.stock', 0)->count();
        $low = (clone $active)->whereBetween('product_variants.stock', [1, DashboardController::LOW_STOCK])->count();

        $sold = $this->unitsSold($range);

        return [
            [
                'label' => 'Units on hand',
                'value' => number_format($onHand),
                'note' => 'Across active sizes',
            ],
            [
                'label' => 'Stock at retail',
                'value' => Product::money($value),
                'note' => 'What the rail is worth',
            ],
            [
                'label' => 'Sell-through',
                'value' => ($sold + $onHand) > 0 ? Stats::share($sold, $sold + $onHand).'%' : '—',
                'note' => number_format($sold).' sold in the period',
            ],
            [
                'label' => 'Needs attention',
                'value' => number_format($out + $low),
                'note' => $out.' sold out, '.$low.' down to their last few',
            ],
        ];
    }

    /**
     * Active pieces holding stock that have never sold a single unit — money
     * sitting on the rail. Deliberately all-time, not windowed: a garment that
     * sold last year is slow, not dead.
     *
     * @return Collection<int, Product>
     */
    private function deadStock(): Collection
    {
        return Product::query()
            ->active()
            ->with('category')
            ->withSum('variants as stock_total', 'stock')
            ->whereHas('variants', fn (Builder $q) => $q->where('stock', '>', 0))
            ->whereNotExists(fn ($q) => $q->from('order_items')
                ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->whereColumn('product_variants.product_id', 'products.id'))
            ->orderByDesc('stock_total')
            ->limit(8)
            ->get();
    }

    /**
     * @return Collection<int, array{label: string, units: int}>
     */
    private function stockByCategory(): Collection
    {
        $roots = $this->rootMap();

        $rows = ProductVariant::query()
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('product_variants.is_active', true)
            ->where('products.is_active', true)
            ->selectRaw('products.category_id as category_id, COALESCE(SUM(product_variants.stock), 0) as units')
            ->groupBy('products.category_id')
            ->get();

        $totals = [];

        foreach ($rows as $row) {
            $label = $roots[$row->category_id] ?? 'Uncategorised';

            $totals[$label] ??= ['label' => $label, 'units' => 0];
            $totals[$label]['units'] += (int) $row->units;
        }

        return collect($totals)->sortByDesc('units')->values();
    }

    // -----------------------------------------------------------------------
    // Shared pieces
    // -----------------------------------------------------------------------

    private function sort(Request $request): string
    {
        $sort = (string) $request->input('sort', 'views');

        return in_array($sort, ['views', 'bag', 'sold', 'saved'], true) ? $sort : 'views';
    }

    /**
     * The catalogue with every engagement and sales figure attached, ready to
     * be ordered by any of them.
     *
     * @return Builder<Product>
     */
    private function rankedProducts(DateRange $range): Builder
    {
        return Product::query()
            ->select('products.*')
            ->with('category')
            ->withEngagement($range->start(), $range->end())
            ->addSelect(['units_sold' => $this->soldLines($range)
                ->selectRaw('COALESCE(SUM(order_items.quantity), 0)')
                ->whereColumn('product_variants.product_id', 'products.id'),
            ])
            ->addSelect(['revenue_sold' => $this->soldLines($range)
                ->selectRaw('COALESCE(SUM(order_items.line_total), 0)')
                ->whereColumn('product_variants.product_id', 'products.id'),
            ]);
    }

    /**
     * Order lines that count as a sale in the window, joined back to the
     * variant they were cut from. The base every product- and category-level
     * sales figure on the page is built on.
     *
     * @return Builder<OrderItem>
     */
    private function soldLines(DateRange $range): Builder
    {
        return $range->apply(
            OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->whereIn('orders.status', OrderStatus::revenueValues()),
            'orders.created_at',
        );
    }

    private function revenue(DateRange $range): float
    {
        return (float) $range->apply(Order::revenue())->sum('grand_total');
    }

    private function orderCount(DateRange $range): int
    {
        return $range->apply(Order::query())->count();
    }

    private function newCustomers(DateRange $range): int
    {
        return $range->apply(Customer::query())->count();
    }

    private function events(DateRange $range, ProductEventType $type): int
    {
        return $range->apply(ProductEvent::query())->where('type', $type)->count();
    }

    private function unitsSold(DateRange $range): int
    {
        return (int) $this->soldLines($range)->sum('order_items.quantity');
    }

    /**
     * Every category id mapped to the name of the section it sits under, so a
     * breakdown can roll leaves up to the five or six headings the shop is
     * actually merchandised by. One query, walked in memory.
     *
     * @return array<int, string>
     */
    private function rootMap(): array
    {
        $categories = Category::query()->get(['id', 'parent_id', 'name'])->keyBy('id');

        $rootOf = function (int $id) use ($categories) {
            $seen = [];

            while (isset($categories[$id]) && $categories[$id]->parent_id && ! isset($seen[$id])) {
                $seen[$id] = true;
                $id = $categories[$id]->parent_id;
            }

            return $categories[$id]->name ?? 'Uncategorised';
        };

        return $categories->mapWithKeys(fn (Category $c) => [$c->id => $rootOf($c->id)])->all();
    }
}

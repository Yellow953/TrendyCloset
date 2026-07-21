<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\ProductFavorite;
use App\Models\ProductVariant;
use App\Services\ProductAnalytics;
use App\Support\Cart;
use App\Support\Catalog;
use App\Support\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Trendy Closet storefront. Catalogue content (categories, products, imagery,
 * pricing, stock) is read from the database; only the editorial furniture that
 * has no table behind it — hero slides, the promise marquee, testimonials — is
 * still hard-coded here, via the img() helper.
 */
class StoreController extends Controller
{
    /** How many products a listing page shows. */
    private const PER_PAGE = 12;

    public function __construct(private readonly Catalog $catalog) {}

    /**
     * Build an Unsplash image descriptor (url + credit) for editorial imagery.
     */
    private function img(string $id, ?string $author = null, ?string $slug = null, int $w = 900, ?int $h = null): array
    {
        // A height forces Unsplash to crop to that aspect — the hero needs a
        // landscape frame, not a portrait one squeezed into a wide band.
        $size = $h ? "w={$w}&h={$h}" : "w={$w}";

        return [
            'img' => "https://images.unsplash.com/{$id}?q=60&{$size}&auto=format&fit=crop",
            // Unattributed ids get a neutral credit rather than a made-up name.
            'credit' => $author ? "Photo by {$author} on Unsplash" : 'Photo via Unsplash',
            'credit_href' => $slug ? "https://unsplash.com/@{$slug}" : 'https://unsplash.com',
        ];
    }

    /**
     * Rotating hero slides. Editorial copy + imagery (no table behind it), but
     * every slide points at a real edit or category.
     *
     * @return array<int, array<string, string>>
     */
    private function heroSlides(): array
    {
        return [
            [
                'eyebrow' => 'TRENDY CLOSET · SUMMER '.now()->year,
                'title' => 'Wear the pieces',
                'accent' => 'everyone asks about',
                'copy' => "Hand-picked by Leila and styled before it ever ships. New drops every Friday.",
                'cta' => 'Shop New In',
                'href' => route('listing', ['edit' => 'new']),
            ] + $this->img('photo-1552374196-1ab2a1c593e8', w: 1400, h: 1200),
            [
                'eyebrow' => 'THE SALE IS LIVE',
                'title' => 'Up to 40% off',
                'accent' => 'your summer favourites',
                'copy' => 'Marked-down pieces from every section — while sizes last.',
                'cta' => 'Shop Sale',
                'href' => route('listing', ['edit' => 'sale']),
            ] + $this->img('photo-1558769132-cb1aea458c5e', w: 1400, h: 1200),
            [
                'eyebrow' => "LEILA'S PICKS",
                'title' => 'Styled by Leila,',
                'accent' => 'worn by you',
                'copy' => 'The edit she keeps restyling — the pieces that go with everything.',
                'cta' => 'Shop the edit',
                'href' => route('listing', ['edit' => 'featured']),
            ] + $this->img('photo-1523381210434-271e8be1f52b', w: 1400, h: 1200),
        ];
    }

    /**
     * The scrolling promise band between sections.
     *
     * @return array<int, string>
     */
    private function marquee(): array
    {
        return [
            'Free shipping over '.Product::money(Cart::FREE_SHIPPING_THRESHOLD),
            '30-day easy returns',
            'Personal styling with Leila',
            'New drops every Friday',
            'Secure encrypted checkout',
        ];
    }

    /**
     * Customer words. Editorial for now — there is no reviews table yet.
     *
     * @return array<int, array<string, string|int>>
     */
    private function testimonials(): array
    {
        return [
            [
                'quote' => "I ordered the wrap dress for a wedding and three people asked where it was from before dessert. The fit is exactly what Leila said it would be.",
                'name' => 'Nour H.',
                'meta' => 'Beirut · 4 orders',
                'stars' => 5,
            ],
            [
                'quote' => "Sizing advice over DM, shipped the next morning, and the knit is genuinely the softest thing I own. This is how online shopping should feel.",
                'name' => 'Marie L.',
                'meta' => 'Paris · 2 orders',
                'stars' => 5,
            ],
            [
                'quote' => "I keep coming back for the basics. Nothing has pilled or lost shape after a whole season of washing, which I cannot say for anything else in my closet.",
                'name' => 'Sara K.',
                'meta' => 'Dubai · 6 orders',
                'stars' => 4,
            ],
        ];
    }

    public function home()
    {
        $featured = Product::query()
            ->active()
            ->featured()
            ->with(['images', 'variants'])
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $deals = Product::query()
            ->active()
            ->onDeal()
            ->with(['images', 'variants'])
            ->orderBy('sale_ends_at')
            ->take(8)
            ->get();

        // The countdown tracks the deal that expires soonest.
        $dealEndsAt = $deals->min('sale_ends_at');
        $countdown = $this->countdown($dealEndsAt);

        $categories = $this->catalog->flat();
        $counts = $this->catalog->counts();

        $promos = $this->promos();

        return view('store.home', [
            'categories' => $categories,
            'counts' => $counts,
            'featured' => $featured,
            'deals' => $deals,
            'dealEndsAt' => $dealEndsAt,
            'countdown' => $countdown,
            'storeRating' => round((float) Product::query()->active()->avg('rating'), 1),
            'promos' => $promos,
            'catalogSize' => Product::query()->active()->count(),
            'heroSlides' => $this->heroSlides(),
            'marquee' => $this->marquee(),
            'testimonials' => $this->testimonials(),
            'active' => 'home',
        ]);
    }

    /**
     * Server-rendered starting values for the Deal of the Week clock; the
     * ticker in resources/js/app.js takes over from the ISO target date, so the
     * numbers are correct even with JavaScript off.
     *
     * @return array<int, array{k: string, l: string, n: string}>
     */
    private function countdown(?\DateTimeInterface $endsAt): array
    {
        if (! $endsAt || $endsAt < now()) {
            return [];
        }

        $diff = now()->diff($endsAt);

        return [
            ['k' => 'days', 'l' => 'DAYS', 'n' => $diff->days],
            ['k' => 'hours', 'l' => 'HOURS', 'n' => $diff->h],
            ['k' => 'minutes', 'l' => 'MINS', 'n' => $diff->i],
            ['k' => 'seconds', 'l' => 'SECS', 'n' => $diff->s],
        ];
    }

    /**
     * The two banner tiles under Featured Products. Each is a real category —
     * its own image, its own live "starting at" price.
     *
     * @return array<int, array{category: Category, from: ?string, eyebrow: string}>
     */
    private function promos(): array
    {
        $preferred = ['summer-section', 'winter-section'];

        $tree = $this->catalog->tree();
        $chosen = collect($preferred)
            ->map(fn (string $slug) => $tree->firstWhere('slug', $slug))
            ->filter()
            ->take(2);

        if ($chosen->count() < 2) {
            $chosen = $tree->take(2);
        }

        return $chosen->map(function (Category $category) {
            $from = Product::query()->active()->inCategory($category)->min('price');

            return [
                'category' => $category,
                'from' => $from !== null ? Product::money($from) : null,
                'eyebrow' => 'BEST COLLECTION',
            ];
        })->values()->all();
    }

    /**
     * Product listing. Doubles as the category page (`/shop/{category}`) and as
     * the New in / Sale edits (`?edit=new|sale`).
     */
    public function listing(Request $request, ?Category $category = null)
    {
        abort_if($category && ! $category->is_active, 404);

        $edit = in_array($request->query('edit'), ['new', 'sale', 'featured'], true)
            ? $request->query('edit')
            : null;

        // The facet lists describe the products in *scope* (category + edit),
        // before the shopper's own size/colour/price choices narrow them —
        // otherwise picking "M" would hide every other size from the sidebar.
        $scope = fn () => Product::query()
            ->active()
            ->when($category, fn ($q) => $q->inCategory($category))
            ->when($edit === 'new', fn ($q) => $q->newArrivals())
            ->when($edit === 'sale', fn ($q) => $q->onSale())
            ->when($edit === 'featured', fn ($q) => $q->featured());

        $scopeIds = $scope()->pluck('id');
        $facets = $this->facets($scopeIds);

        $size = $request->query('size');
        $color = $request->query('color');
        $sort = $request->query('sort', 'popular');

        $products = $scope()
            ->with(['images', 'variants'])
            ->when($size, fn ($q) => $q->whereHas('variants', fn ($v) => $v->where('size', $size)->where('is_active', true)))
            ->when($color, fn ($q) => $q->whereHas('variants', fn ($v) => $v->where('color', $color)->where('is_active', true)))
            ->when($request->filled('max'), fn ($q) => $q->where('price', '<=', (float) $request->query('max')))
            ->when($request->filled('min'), fn ($q) => $q->where('price', '>=', (float) $request->query('min')))
            ->tap(fn ($q) => $this->applySort($q, $sort))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('store.listing', [
            // The sidebar walks the same tree the header does.
            'navTree' => $this->catalog->tree(),
            'catalog' => $this->catalog,
            'category' => $category,
            'edit' => $edit,
            'products' => $products,
            'sizes' => $facets['sizes'],
            'colors' => $facets['colors'],
            'priceFloor' => $facets['min'],
            'priceCeiling' => $facets['max'],
            'filters' => [
                'size' => $size,
                'color' => $color,
                'min' => $request->query('min'),
                'max' => $request->query('max'),
                'sort' => $sort,
            ],
            'sideBanner' => $this->img('photo-1470309864661-68328b2cd0a5', 'Artificial Photography', 'artificialphotography', 600),
            'active' => $category ? 'shop' : ($edit ?? 'shop'),
        ]);
    }

    /**
     * Size / colour / price facets for a set of products.
     *
     * @param  Collection<int, int>  $productIds
     * @return array{sizes: Collection<int, string>, colors: Collection<int, string>, min: float, max: float}
     */
    private function facets(Collection $productIds): array
    {
        $variants = ProductVariant::query()
            ->whereIn('product_id', $productIds)
            ->where('is_active', true)
            ->get(['size', 'color']);

        $bounds = Product::query()
            ->whereIn('id', $productIds)
            ->selectRaw('MIN(price) as low, MAX(price) as high')
            ->first();

        return [
            'sizes' => ProductVariant::sortSizes($variants->pluck('size')->filter()->unique()->values()),
            'colors' => $variants->pluck('color')->filter()->unique()->sort()->values(),
            'min' => (float) ($bounds->low ?? 0),
            'max' => (float) ($bounds->high ?? 0),
        ];
    }

    /**
     * "Most popular" is real engagement — the view counts recorded by
     * ProductAnalytics — not a hard-coded order.
     */
    private function applySort($query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'price-asc' => $query->orderBy('price'),
            'price-desc' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating')->orderByDesc('id'),
            default => $query->withEngagement()->orderByDesc('views_count')->orderByDesc('id'),
        };
    }

    public function product(Product $product, ProductAnalytics $analytics)
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'variants', 'category.parent']);

        $analytics->recordView($product);

        // Same category first; top up from the wider catalogue so the rail is
        // never half-empty in a thinly stocked corner of the store.
        $related = Product::query()
            ->active()
            ->where('id', '!=', $product->id)
            ->when($product->category, fn ($q) => $q->where('category_id', $product->category_id))
            ->with(['images', 'variants'])
            ->take(4)
            ->get();

        if ($related->count() < 4) {
            $related = $related->concat(
                Product::query()
                    ->active()
                    ->featured()
                    ->whereNotIn('id', $related->pluck('id')->push($product->id))
                    ->with(['images', 'variants'])
                    ->take(4 - $related->count())
                    ->get()
            );
        }

        $variants = $product->variants->where('is_active', true);

        return view('store.product', [
            'product' => $product,
            'gallery' => $product->images->sortBy('position')->values(),
            'sizes' => ProductVariant::sortSizes($variants->pluck('size')->filter()->unique()->values()),
            'variants' => $variants->values(),
            'colors' => $variants->pluck('color')->filter()->unique()->values(),
            'related' => $related,
            'favorited' => $analytics->hasFavorited($product),
            'favoritesCountForProduct' => $product->favorites()->count(),
            // Real urgency, from the analytics log — not an invented number.
            'recentAdds' => $product->events()
                ->where('type', \App\Enums\ProductEventType::AddToCart)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'breadcrumb' => array_filter([$product->category?->parent, $product->category]),
            'active' => 'shop',
        ]);
    }

    /**
     * Toggle the heart. Favourites are per-visitor state, not an event — see
     * the analytics notes in CLAUDE.md.
     */
    public function favorite(Product $product, ProductAnalytics $analytics)
    {
        abort_unless($product->is_active, 404);

        $favorited = $analytics->toggleFavorite($product);

        return back()->with('status', $favorited
            ? 'Saved to your favourites.'
            : 'Removed from your favourites.');
    }

    /**
     * Everything the current visitor has hearted.
     */
    public function favorites(Visitor $visitor)
    {
        $products = Product::query()
            ->active()
            ->whereIn('id', ProductFavorite::where('visitor_id', $visitor->id)->select('product_id'))
            ->with(['images', 'variants'])
            ->get();

        return view('store.favorites', [
            'products' => $products,
            'active' => null,
        ]);
    }

    public function about()
    {
        return view('store.about', [
            'hero' => $this->img('photo-1490481651871-ab68de25d43d', 'Priscilla Du Preez', 'priscilladupreez', 1400),
            'portrait' => $this->img('photo-1544441893-675973e31985', 'Mnz', 'mnzoutfits', 800),
            'catalogSize' => Product::query()->active()->count(),
            'categoryCount' => $this->catalog->tree()->count(),
            'storeRating' => round((float) Product::query()->active()->avg('rating'), 1),
            // A small rail of real product so the page is not pure editorial.
            'picks' => Product::query()
                ->active()
                ->featured()
                ->with(['images', 'variants'])
                ->orderByDesc('id')
                ->take(4)
                ->get(),
            'active' => 'about',
        ]);
    }

    public function contact()
    {
        return view('store.contact', ['active' => 'contact']);
    }

    /**
     * Contact form → `contact_messages`, where the back-office CRM will read it.
     */
    public function sendContact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($data);

        return back()->with('status', 'Thanks — your message is with Leila. We reply within 24 hours.');
    }

    /**
     * The policy pages. One route, five documents — `/policies/{topic}` — with
     * a shared sidebar so they read as one section of the site.
     */
    public function policies(?string $topic = null)
    {
        $topics = $this->policyTopics();
        $topic ??= array_key_first($topics);

        abort_unless(isset($topics[$topic]), 404);

        return view('store.policies', [
            'topics' => $topics,
            'current' => $topic,
            'page' => $topics[$topic],
            // The size guide tabulates the size runs actually in the catalogue.
            'sizeRuns' => $topic === 'size-guide' ? $this->sizeRuns() : [],
            'active' => 'policies',
        ]);
    }

    /**
     * @return array<string, array{title: string, intro: string, sections: array<int, array{heading: string, body: string}>}>
     */
    private function policyTopics(): array
    {
        $free = Product::money(Cart::FREE_SHIPPING_THRESHOLD);
        $flat = Product::money(Cart::STANDARD_SHIPPING);

        return [
            'shipping' => [
                'title' => 'Shipping & Delivery',
                'intro' => "Where your order goes, how long it takes, and what it costs.",
                'sections' => [
                    ['heading' => 'Rates', 'body' => "Standard delivery is {$flat} and free on orders over {$free}. Express delivery is \$9.00 and is offered at checkout wherever our courier supports it."],
                    ['heading' => 'Processing time', 'body' => 'Orders placed before 2pm on a working day are packed the same day. Orders ship Monday to Friday, excluding public holidays.'],
                    ['heading' => 'Delivery windows', 'body' => 'Standard delivery arrives in 3–5 business days; express in 1–2. Remote addresses can add a day or two beyond the courier estimate.'],
                    ['heading' => 'Tracking', 'body' => 'A tracking link is emailed the moment your parcel is collected. If it has not moved for 48 hours, write to us and we will chase the courier for you.'],
                    ['heading' => 'Customs & duties', 'body' => 'International orders may attract import duties set by your own country. These are payable by the recipient and are not included in the price at checkout.'],
                ],
            ],
            'returns' => [
                'title' => 'Returns & Refunds',
                'intro' => 'Thirty days to change your mind, on almost everything.',
                'sections' => [
                    ['heading' => 'The window', 'body' => 'You have 30 days from delivery to start a return. Items must be unworn, unwashed and still have their tags attached.'],
                    ['heading' => 'How to start one', 'body' => 'Email hello@trendycloset.com with your order number and which pieces are going back. We reply with a return label and instructions within one working day.'],
                    ['heading' => 'Refunds', 'body' => 'Refunds are issued to the original payment method within 5 working days of your parcel reaching us. Your bank may take a few days more to show it.'],
                    ['heading' => 'Exchanges', 'body' => 'The fastest exchange is a return plus a fresh order — that way your new size is reserved immediately rather than waiting on the return to land.'],
                    ['heading' => 'What cannot be returned', 'body' => 'Final-sale pieces, pierced jewellery and swimwear with the hygiene strip removed cannot be returned unless they arrive faulty.'],
                    ['heading' => 'Faulty items', 'body' => 'If something arrives damaged or develops a fault, send us a photo. We cover return postage and either replace the piece or refund it in full.'],
                ],
            ],
            'size-guide' => [
                'title' => 'Size Guide',
                'intro' => 'Our pieces run true to size. Measure over your underwear, keeping the tape level and snug.',
                'sections' => [
                    ['heading' => 'How to measure', 'body' => 'Bust: around the fullest part, arms down. Waist: around the narrowest part of your torso. Hips: around the fullest part, roughly 20 cm below the waist.'],
                    ['heading' => 'Between sizes?', 'body' => 'Size up for knitwear and outerwear, and stay true to size for jersey and stretch denim. Leila is 172 cm and wears a S / waist 26.'],
                    ['heading' => 'Still unsure', 'body' => 'DM @trendycloset.byleilakonsol or use the contact form with your measurements and the piece you are looking at — we answer sizing questions within 24 hours.'],
                ],
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'intro' => 'What we collect, why we collect it, and what we never do with it.',
                'sections' => [
                    ['heading' => 'What we collect', 'body' => 'To fulfil an order we keep your name, email, delivery address and phone number. Payment card details are handled by our payment processor and never reach our servers.'],
                    ['heading' => 'Browsing data', 'body' => 'We set a long-lived cookie so your bag and favourites survive between visits, and we count product views to see which pieces resonate. This is tied to a random identifier, not to your identity.'],
                    ['heading' => 'Marketing', 'body' => 'We email you only if you asked us to. Every newsletter carries a one-click unsubscribe, and we never sell or rent your details to anyone.'],
                    ['heading' => 'Your rights', 'body' => 'You can ask for a copy of everything we hold about you, ask us to correct it, or ask us to delete it. Write to hello@trendycloset.com and we will action it within 30 days.'],
                    ['heading' => 'Retention', 'body' => 'Order records are kept for as long as tax law requires. Everything else is deleted once it stops being useful to you as a customer.'],
                ],
            ],
            'terms' => [
                'title' => 'Terms of Service',
                'intro' => 'The agreement between you and Trendy Closet when you shop with us.',
                'sections' => [
                    ['heading' => 'Orders', 'body' => 'An order is an offer to buy. The contract forms when we email you to confirm dispatch — until then we may decline an order, for example if a piece has sold out.'],
                    ['heading' => 'Pricing', 'body' => 'Prices include VAT where applicable and are shown before you pay. If a piece is listed at an obviously incorrect price we will contact you before charging anything.'],
                    ['heading' => 'Product imagery', 'body' => 'We photograph pieces as accurately as we can, but screens differ. Colour variation between your screen and the garment is not itself a fault.'],
                    ['heading' => 'Discount codes', 'body' => 'One code per order unless stated otherwise. Codes carry no cash value and may be withdrawn at any time before an order is placed.'],
                    ['heading' => 'Liability', 'body' => 'Nothing here limits your statutory rights as a consumer. Our liability for any order is limited to the value of that order.'],
                    ['heading' => 'Governing law', 'body' => 'These terms are governed by French law, and disputes fall to the courts of Paris.'],
                ],
            ],
        ];
    }

    /**
     * The size runs actually stocked, for the size-guide table.
     *
     * @return array<string, Collection<int, string>>
     */
    private function sizeRuns(): array
    {
        $sizes = ProductVariant::query()
            ->where('is_active', true)
            ->distinct()
            ->pluck('size')
            ->filter();

        return [
            'Clothing' => ProductVariant::sortSizes($sizes->reject(fn ($s) => is_numeric($s))->values()),
            'Denim & trousers (waist)' => ProductVariant::sortSizes($sizes->filter(fn ($s) => is_numeric($s))->values()),
        ];
    }
}

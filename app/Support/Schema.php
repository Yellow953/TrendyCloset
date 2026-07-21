<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Builders for the JSON-LD nodes the storefront publishes.
 *
 * Every node is assembled from real data. Deliberately absent: `aggregateRating`
 * and `review` — there is no reviews table, and inventing rating counts to win a
 * stars snippet is exactly what earns a structured-data manual action. Add them
 * the day real reviews exist, not before.
 *
 * Nodes are stitched into one `@graph` by Seo::jsonLd(), and cross-reference
 * each other by `@id` so a crawler reads the page as one connected description
 * rather than a pile of unrelated objects.
 */
class Schema
{
    /** Stable @id for the store, referenced as publisher/seller/brand. */
    public static function organizationId(): string
    {
        return url('/').'#organization';
    }

    public static function websiteId(): string
    {
        return url('/').'#website';
    }

    /**
     * The store itself. `sameAs` is what ties this site to its social profiles
     * in an engine's entity graph — the single highest-value node here.
     *
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $social = array_values(array_filter(config('seo.social', [])));

        return array_filter([
            '@type' => 'OnlineStore',
            '@id' => self::organizationId(),
            'name' => config('seo.brand_full'),
            'alternateName' => config('seo.brand'),
            'url' => url('/'),
            'description' => config('seo.description'),
            'slogan' => config('seo.tagline'),
            'email' => config('seo.email'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('images/logo-512.png'),
            ],
            'image' => asset('images/logo-512.png'),
            'founder' => [
                '@type' => 'Person',
                'name' => config('seo.founder'),
            ],
            'sameAs' => $social ?: null,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'email' => config('seo.email'),
                'availableLanguage' => ['English', 'French'],
            ],
        ]);
    }

    /**
     * The site. No `SearchAction` — the storefront has no search endpoint yet,
     * and advertising one that 404s is worse than advertising none.
     *
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => self::websiteId(),
            'url' => url('/'),
            'name' => config('seo.brand_full'),
            'description' => config('seo.description'),
            'publisher' => ['@id' => self::organizationId()],
            'inLanguage' => 'en',
        ];
    }

    /**
     * Breadcrumbs. Pass the trail *without* Home (added here) and without the
     * current page's own URL — the last crumb is the page you are on.
     *
     * @param  array<int, array{name: string, url: ?string}>  $trail
     * @return array<string, mixed>|null
     */
    public static function breadcrumbs(array $trail): ?array
    {
        if ($trail === []) {
            return null;
        }

        array_unshift($trail, ['name' => 'Home', 'url' => route('home')]);

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($trail)->values()->map(fn (array $crumb, int $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'] ?? null,
            ]))->all(),
        ];
    }

    /**
     * A single product, with its live price, stock and returns terms.
     *
     * @return array<string, mixed>
     */
    public static function product(Product $product): array
    {
        $url = route('product', $product);
        $images = $product->images->sortBy('position')->pluck('url')->filter()->values();

        return array_filter([
            '@type' => 'Product',
            '@id' => $url.'#product',
            'name' => $product->name,
            'description' => self::text($product->description) ?: $product->name.' from '.config('seo.brand').'.',
            'url' => $url,
            'image' => $images->isNotEmpty() ? $images->all() : null,
            'sku' => 'TC-'.$product->id,
            'category' => $product->category?->name,
            'brand' => [
                '@type' => 'Brand',
                'name' => config('seo.brand'),
            ],
            // Sizes and colours actually stocked, so an engine can answer
            // "does this come in a medium?" without loading the page.
            'size' => self::variantValues($product, 'size'),
            'color' => self::variantValues($product, 'color'),
            'offers' => self::offer($product, $url),
        ]);
    }

    /**
     * The buyable terms for a product: price, availability, and the shipping
     * and returns policies that the /policies pages state in prose.
     *
     * @return array<string, mixed>
     */
    private static function offer(Product $product, string $url): array
    {
        return array_filter([
            '@type' => 'Offer',
            'url' => $url,
            'price' => number_format((float) $product->price, 2, '.', ''),
            'priceCurrency' => config('seo.currency'),
            'availability' => $product->in_stock
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            // A deal price is only good until the countdown runs out.
            'priceValidUntil' => $product->sale_ends_at?->toDateString(),
            'seller' => ['@id' => self::organizationId()],
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy',
                'applicableCountry' => 'FR',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays' => 30,
                'returnMethod' => 'https://schema.org/ReturnByMail',
                'returnFees' => 'https://schema.org/FreeReturn',
                'merchantReturnLink' => route('policies', 'returns'),
            ],
            'shippingDetails' => [
                '@type' => 'OfferShippingDetails',
                'shippingRate' => [
                    '@type' => 'MonetaryAmount',
                    'value' => number_format(Cart::STANDARD_SHIPPING, 2, '.', ''),
                    'currency' => config('seo.currency'),
                ],
                'deliveryTime' => [
                    '@type' => 'ShippingDeliveryTime',
                    // Orders before 2pm pack same day; 3-5 business days in transit.
                    'handlingTime' => self::window(0, 1),
                    'transitTime' => self::window(3, 5),
                ],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function window(int $min, int $max): array
    {
        return [
            '@type' => 'QuantitativeValue',
            'minValue' => $min,
            'maxValue' => $max,
            'unitCode' => 'DAY',
        ];
    }

    /**
     * A listing page as an ordered list of product URLs. Engines use this to
     * understand what a category *contains* without crawling every child.
     *
     * @param  Collection<int, Product>  $products
     * @return array<string, mixed>|null
     */
    public static function itemList(Collection $products, string $name): ?array
    {
        if ($products->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => $products->count(),
            'itemListElement' => $products->values()->map(fn (Product $p, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => route('product', $p),
                'name' => $p->name,
            ])->all(),
        ];
    }

    /**
     * A category as a collection page, so `/shop/dresses` reads as a subject in
     * its own right rather than just a container of links.
     *
     * @return array<string, mixed>
     */
    public static function collectionPage(string $name, string $url, ?string $description = null): array
    {
        return array_filter([
            '@type' => 'CollectionPage',
            '@id' => $url.'#collection',
            'name' => $name,
            'url' => $url,
            'description' => self::text($description),
            'isPartOf' => ['@id' => self::websiteId()],
        ]);
    }

    /**
     * Question/answer pairs. This is the node generative engines quote from
     * most readily, because it is already shaped like an answer.
     *
     * @param  array<int, array{question: string, answer: string}>  $faqs
     * @return array<string, mixed>|null
     */
    public static function faq(array $faqs): ?array
    {
        if ($faqs === []) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => self::text($faq['answer']),
                ],
            ])->all(),
        ];
    }

    /**
     * A prose page (About, a policy document) as an article-like entity.
     *
     * @return array<string, mixed>
     */
    public static function webPage(string $name, string $url, ?string $description = null): array
    {
        return array_filter([
            '@type' => 'WebPage',
            '@id' => $url.'#webpage',
            'name' => $name,
            'url' => $url,
            'description' => self::text($description),
            'isPartOf' => ['@id' => self::websiteId()],
            'publisher' => ['@id' => self::organizationId()],
        ]);
    }

    /**
     * Distinct active variant values (sizes, colours) for a product.
     *
     * @return array<int, string>|null
     */
    private static function variantValues(Product $product, string $column): ?array
    {
        $values = $product->variants
            ->where('is_active', true)
            ->pluck($column)
            ->filter()
            ->unique()
            ->values();

        return $values->isNotEmpty() ? $values->all() : null;
    }

    /**
     * Collapse free text to a single clean line — JSON-LD values carry no markup.
     */
    private static function text(?string $value): ?string
    {
        $value = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));

        return $value === '' ? null : Str::limit($value, 500);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\Cart;
use App\Support\Catalog;
use Illuminate\Http\Response;

/**
 * The machine-readable face of the storefront: what crawlers and LLM agents
 * fetch instead of a page.
 *
 * `robots.txt` is served from here rather than public/ because it has to name
 * the sitemap at the *current* domain — a static file would have to hard-code
 * one, and would be wrong in every environment but production.
 */
class SeoController extends Controller
{
    public function __construct(private readonly Catalog $catalog) {}

    /**
     * Crawl directives. Everything behind a session — the bag, checkout, saved
     * favourites, admin auth — is closed off; the catalogue is wide open.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            '# Session-scoped or private — nothing here is a landing page.',
            'Disallow: /bag',
            'Disallow: /checkout',
            'Disallow: /favorites',
            'Disallow: /login',
            'Disallow: /password/',
            '',
            '# Filtered listings (?size=, ?color=, ?min=, ?max=, ?sort=) are NOT',
            '# blocked here on purpose. They send "noindex" and canonicalise back',
            '# to the clean category URL — but a crawler has to be allowed to',
            '# fetch a page before it can read either signal. Blocking them would',
            '# leave the duplicates indexable from internal links alone.',
            '',
            '# A plain-language summary of this store for LLM agents.',
            '# '.url('/llms.txt'),
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Every indexable URL, with a lastmod a crawler can trust. Session pages and
     * filtered listings are deliberately absent — a sitemap should list what you
     * want indexed, not everything that returns 200.
     */
    public function sitemap(): Response
    {
        $urls = [];

        $add = function (string $loc, ?\DateTimeInterface $lastmod, string $freq, string $priority) use (&$urls) {
            $urls[] = compact('loc', 'lastmod', 'freq', 'priority');
        };

        // The catalogue's freshest product stands in as the shop's lastmod.
        $newest = Product::query()->active()->max('updated_at');
        $newest = $newest ? new \DateTimeImmutable($newest) : null;

        $add(route('home'), $newest, 'daily', '1.0');
        $add(route('listing'), $newest, 'daily', '0.9');

        foreach (['new', 'sale', 'featured'] as $edit) {
            $add(route('listing', ['edit' => $edit]), $newest, 'daily', '0.8');
        }

        foreach (Category::query()->active()->ordered()->get() as $category) {
            $add(route('listing', $category), $category->updated_at, 'weekly', '0.8');
        }

        Product::query()
            ->active()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($products) use ($add) {
                foreach ($products as $product) {
                    $add(route('product', $product), $product->updated_at, 'weekly', '0.7');
                }
            });

        $add(route('about'), null, 'monthly', '0.5');
        $add(route('contact'), null, 'monthly', '0.5');

        foreach (['shipping', 'returns', 'size-guide', 'privacy', 'terms'] as $topic) {
            $add(route('policies', $topic), null, 'yearly', '0.4');
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * llms.txt — the emerging convention for telling an LLM agent, in prose,
     * what a site is and where its useful pages live, without making it infer
     * all of that from marked-up HTML.
     *
     * It is generated from live data for the same reason the meta descriptions
     * are: a hand-written summary goes stale the first time the catalogue does.
     */
    public function llms(): Response
    {
        $brand = config('seo.brand_full');
        $free = Product::money(Cart::FREE_SHIPPING_THRESHOLD);
        $flat = Product::money(Cart::STANDARD_SHIPPING);
        $count = Product::query()->active()->count();
        $from = Product::query()->active()->min('price');
        $to = Product::query()->active()->max('price');

        $out = [];
        $out[] = "# {$brand}";
        $out[] = '';
        $out[] = '> '.config('seo.description');
        $out[] = '';
        $out[] = 'An independent online fashion boutique founded and curated by '.config('seo.founder')
            .". Every piece is selected, fitted and photographed before it is listed. The catalogue currently holds "
            ."{$count} active pieces"
            .($from !== null ? ' priced from '.Product::money($from).' to '.Product::money($to) : '').'.';
        $out[] = '';

        $out[] = '## Shopping';
        $out[] = '';
        $out[] = '- Currency: '.config('seo.currency').'. Prices shown include VAT where applicable.';
        $out[] = "- Standard delivery {$flat}, free over {$free}, arriving in 3–5 business days. Express delivery is offered at checkout where the courier supports it.";
        $out[] = '- Returns: 30 days from delivery, unworn with tags, return postage covered. Refunds within 5 working days.';
        $out[] = '- Sizing runs true to size; size up for knitwear and outerwear.';
        $out[] = '- Customers check out as guests — there is no shopper account to create.';
        $out[] = '- Contact: '.config('seo.email').'. Questions are answered within 24 hours.';
        $out[] = '';

        $out[] = '## Key pages';
        $out[] = '';
        $out[] = '- [Home]('.route('home').'): featured pieces, current edits and the deal of the week.';
        $out[] = '- [Shop all]('.route('listing').'): the full catalogue, filterable by size, colour and price.';
        $out[] = '- [New in]('.route('listing', ['edit' => 'new']).'): the most recent arrivals.';
        $out[] = '- [Sale]('.route('listing', ['edit' => 'sale']).'): everything currently discounted.';
        $out[] = '- [Our story]('.route('about').'): who Leila Konsol is and how the shop is curated.';
        $out[] = '- [Contact]('.route('contact').'): email, WhatsApp and the contact form.';
        $out[] = '';

        $out[] = '## Categories';
        $out[] = '';
        foreach ($this->catalog->tree() as $root) {
            $children = $root->children->pluck('name')->implode(', ');
            $out[] = '- ['.$root->name.']('.route('listing', $root).')'
                .' — '.$this->catalog->countFor($root).' pieces'
                .($children !== '' ? '. Includes: '.$children : '').'.';
        }
        $out[] = '';

        $out[] = '## Customer care';
        $out[] = '';
        foreach (['shipping' => 'Shipping & delivery', 'returns' => 'Returns & refunds', 'size-guide' => 'Size guide', 'privacy' => 'Privacy policy', 'terms' => 'Terms of service'] as $topic => $label) {
            $out[] = "- [{$label}](".route('policies', $topic).')';
        }
        $out[] = '';

        $out[] = '## Notes';
        $out[] = '';
        $out[] = '- Product pages carry schema.org Product markup with live price and stock; treat that as authoritative over any cached figure.';
        $out[] = '- Stock moves daily. Availability stated anywhere else may be out of date.';
        $out[] = '- The bag, checkout and favourites pages are session-specific and are not useful to crawl.';
        $out[] = '';

        return response(implode("\n", $out), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}

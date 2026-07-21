# CLAUDE.md

Guidance for working in this repository.

## Project

**Trendy Closet by Leila Konsol** — a Laravel storefront (marketing/catalog UI) implemented from
the "Storefront Explorations" Claude Design doc. The storefront is now **database-driven**:
categories, products, imagery, pricing, stock, favourites and the bag all come from real data.
What is still hard-coded is the *editorial* furniture with no table behind it (hero collage,
Instagram strip, About copy). **Checkout does not place an order yet** — the payment/address form
is presentational and nothing writes to `orders`.

## Stack

- **Laravel** 13.20 on **PHP** 8.5
- **Blade** templates (layout inheritance + partials)
- **Tailwind CSS v4** (CSS-first config via `@theme` in `resources/css/app.css`; no `tailwind.config.js`)
- **Vite** (`laravel-vite-plugin` + `@tailwindcss/vite`)
- Google Fonts: Jost (sans), Cormorant Garamond (serif), Space Grotesk (display)

## Commands

```bash
npm run dev        # Vite dev server with HMR (run alongside `php artisan serve`)
npm run build      # Production asset build → public/build
php artisan serve  # Local PHP server (http://localhost:8000)
```

**This project does not use automated tests** — `tests/` is intentionally empty (only the Pest
scaffolding remains). Do not add test files; verify changes by exercising the pages instead.

## Architecture

**Routing** — Storefront routes live in `routes/web.php`, split across `StoreController` (pages)
and `CartController` (the bag). Named routes: `home` `/`, `listing` `/shop/{category:slug?}`,
`product` `/product/{product:slug}`, `product.favorite`, `favorites`, `cart` `/bag`, `cart.add` /
`cart.update` / `cart.remove` / `cart.coupon` / `cart.coupon.remove`, `checkout`, `about`,
`contact` + `contact.send`, `policies`. `/women` 301s to `/shop` for old links.
- **One listing action serves everything**: all products, a single category (`/shop/jeans`) and the
  edits (`?edit=new|sale|featured`), so filters and sorting behave identically everywhere. Browsing
  a *parent* category widens to its children via `Product::inCategory()` — products live on leaves.
- Filters are query params: `edit`, `size`, `color`, `min`, `max`, `sort`
  (`popular|newest|price-asc|price-desc|rating`). `popular` sorts by real `views_count` from
  `withEngagement()`, not a hard-coded order.

**Auth** — admin-only, for the (not-yet-built) back-office CRM. `Auth::routes()` was replaced by
explicit route groups; **registration and email verification are removed** — there is no
`register` route and no `RegisterController`. Remaining: `login` / `logout`, `password.request`,
`password.email`, `password.reset`, `password.update`, `password.confirm`. Admin accounts are
created by `DatabaseSeeder` (`ADMIN_EMAIL` / `ADMIN_PASSWORD` / `ADMIN_NAME` in `.env`), never
self-service. Login redirects to `/`.

**Identity — the key architectural split.** `User` is an **internal back-office user only** and is
the only authenticatable model. `Customer` is a plain (non-authenticatable) CRM record with no
password: **customers never sign in**, they check out as guests. Do not add customers to `users`,
and do not add auth traits to `Customer` without revisiting this decision.
- `App\Enums\UserRole` (`admin` | `staff`) is cast on `User->role`. `$user->isAdmin()` delegates to
  `UserRole::managesStore()`. Since only staff can log in, `auth` alone gates the CRM; the `admin`
  middleware alias (`EnsureUserIsAdmin`, registered in `bootstrap/app.php`) is the narrower gate for
  user management, coupons, and store settings.
- `Customer::forEmail($email, $attrs)` matches-or-creates on a normalised (lowercased, trimmed)
  email — use it at checkout so repeat buyers collapse into one record.
- `orders.customer_id` is nullable + `nullOnDelete` so deleting a customer never destroys sales
  history. The `email` / `ship_*` columns on `orders` are a deliberate **snapshot of the order as
  placed** — never refactor them into a join on `customers`.
**The bag** — `App\Support\Cart` (scoped binding, session key `tc_cart`). There is still **no `Cart`
model or `carts` table**: an abandoned bag is not a CRM record, and shoppers check out as guests.
- The session stores only `variant_id => qty` plus a coupon code. Prices, stock and imagery are
  re-read from the catalogue on every request, so a week-old bag can never charge last week's price.
- Quantities are clamped to `variant->stock`; setting a quantity of 0 removes the line. Lines whose
  variant or product went inactive silently drop out of `lines()`.
- `summary()` returns subtotal / discount / shipping / total / coupon in one call. Shipping is free
  at or above `Cart::FREE_SHIPPING_THRESHOLD` (or with a `free_shipping` coupon), else
  `Cart::STANDARD_SHIPPING`. Coupons are re-validated against the live subtotal on every read, so a
  code that stops qualifying simply stops applying.

**Analytics** — product engagement is tracked separately from the bag, split by shape:
- `ProductEvent` (`product_events`) is an **append-only** log of things that happened — `view` and
  `add_to_cart`, typed by `App\Enums\ProductEventType`. No `updated_at`; never mutate a row.
- `ProductFavorite` (`product_favorites`) is **state**, unique per `(product_id, visitor_id)`.
  Unfavouriting deletes the row, so the count is just `COUNT(*)`. Favourites are deliberately not
  events — don't merge these two tables.
- Identity is `visitor_id`: a forever cookie (`tc_visitor`) set by the `TrackVisitor` middleware and
  resolved as `App\Support\Visitor`. **Not** the session id — sessions expire in hours, which would
  fragment one shopper into many visitors and drop their favourites overnight.
- Write via `App\Services\ProductAnalytics` (`recordView`, `recordAddToCart`, `toggleFavorite`,
  `hasFavorited`). Views are deduped per visitor/product for 30 min via the cache so refreshes and
  crawlers don't inflate the numbers; add-to-cart is intentionally not deduped.
- Read via `Product::withEngagement($since = null)` → `views_count`, `add_to_cart_count`,
  `favorites_count`. The window applies to events only, never to favourites.
- Events are kept forever for now — add a prune command if the table outgrows a `GROUP BY`.

**Controllers** — `StoreController` (pages) and `CartController` (bag mutations).
- `StoreController::img($id, $author, $slug, $w)` builds an Unsplash descriptor and is now only for
  **editorial** imagery (hero, Instagram, sale banner). Catalogue imagery comes from
  `product_images` / `categories.image_*`.
- Every action passes `active` (nav highlight key: `home` `shop` `new` `sale` `about` …).
  `bagCount`, `bagTotal`, `navTree`, `catalog` and `favoritesCount` are **not** passed by actions —
  a view composer in `AppServiceProvider` supplies them to `partials.header` / `partials.footer`.
  The listing action passes `navTree`/`catalog` explicitly because its sidebar walks the same tree.

**Shared support** (`app/Support/`) — `Cart`, `Visitor`, plus:
- `Catalog` (scoped) — the navigation tree, flattened list, per-category product counts (a parent's
  count includes its children) and the mega-menu `spotlight()` product. Resolved once per request so
  header, sidebar and home carousel share one set of queries.
- `Swatch` — maps variant colour names to hex for the filter/PDP swatches; unknown colours fall back
  to a neutral chip rather than vanishing.

**Model helpers used by the views** — `Product::money()` formats every price on the site;
`image_url`, `price_label`, `compare_label`, `badge_label` (falls back to a derived `-20%`) and
`in_stock` back the product card and PDP. Scopes: `active`, `featured`, `onDeal`, `onSale`,
`newArrivals`, `inCategory`, `withEngagement`. `ProductVariant::sortSizes()` orders sizes the way a
rail reads (XS→2XL, then numeric waists) and `$variant->label` renders "Size M · Oat".

**Views** — `resources/views/`:
- `layouts/storefront.blade.php` — root layout: `<head>` (fonts + `@vite`), header, `@yield('content')`, footer. **Full-bleed** (no max-width wrapper); each section supplies its own horizontal padding (`px-8 md:px-16`).
- `partials/` — `header`, `footer`, `product-card`, `flash`, `pagination`.
  - `header` is **sticky** (`position: sticky`), with a centred single-line announcement bar that
    collapses on scroll: `initStickyHeader()` toggles `.is-scrolled`, the CSS does the rest. Nav is
    HOME / SHOP (mega-menu from the category tree) / ABOUT / CONTACT; the right side is icon
    actions (search, account, favourites, bag) with count badges.
  - `footer` is light, five columns (About / Shop / Your Account / Services / Contact) with the
    newsletter as its opening band and a bottom bar of socials, copyright and payment marks.
  - `product-card` takes a `Product` (`$p`) and optional height `$h`. On hover it reveals a rail of
    three actions — favourite, quick-add (posts `default_variant`, so eager-load `variants` or the
    button renders disabled), and view.
- `layouts/auth.blade.php` — standalone admin-auth layout (no storefront header/footer): editorial
  image panel on the left (`lg:` only) + form panel on the right. Auth pages fill the
  `eyebrow` / `heading` / `subheading` / `form` sections rather than `content`; `session('status')`
  is rendered by the layout.
- `partials/auth-field.blade.php` — label + `.tc-input` + inline `@error` message. Takes `name`,
  `label`, and optional `type` / `value` / `autocomplete` / `autofocus` / `placeholder`.
- `auth/` — `login`, `passwords/{email,reset,confirm}`, each `@extends('layouts.auth')`.
- `store/` — one Blade file per page (`home`, `listing`, `product`, `cart`, `checkout`, `favorites`,
  `about`, `contact`, `policies`), each `@extends('layouts.storefront')`.
- **Home sections** — a rotating hero (cross-fading slides + dots, `initHero()`; slide one is
  rendered `.is-active` so it works without JS), centred section headings (`.tc-heading` +
  `.tc-heading-rule`), category circles, product carousels, an infinite promise marquee
  (`.tc-marquee`, item list rendered twice so the loop has no seam), promo banners, deal countdown,
  testimonials and the Instagram strip. Hero slides / marquee / testimonials are editorial arrays on
  `StoreController`; everything else is catalogue data.
- **Product page** — thumbnail rail + main image with cursor-tracking hover zoom (`[data-zoom]`,
  `[data-gallery]`), a purchase panel (size radios + `data-clear-target`, `[data-qty]` stepper,
  Add To Bag and Buy Now as two submits on one form — `action=buy` adds then redirects to checkout),
  then a **full-width centred** tab section (`[data-tabs]`; the first panel renders visible so it
  still works with JS off) and a related grid. The "selling fast" line is the real 7-day
  `add_to_cart` count, not an invented number.
- **Sticky buy bar** — `[data-sticky-buy]` slides up once `[data-buy-form]` scrolls past; its size
  select stays in sync with the radios above. It also sets `.has-sticky-buy` on `<body>` so the
  floating WhatsApp button lifts clear of it.
- **WhatsApp** — `partials/whatsapp.blade.php` renders a floating button on every storefront page
  from `config/store.php` (`WHATSAPP_NUMBER` / `WHATSAPP_MESSAGE`); an empty number hides it.
- **Blade gotcha:** the inline `@php(...)` form has miscompiled here (emitting `<?php(...)` with no
  closing tag, which swallows the rest of the file). Prefer a `@php ... @endphp` block, and keep it
  **inside** `@section`.
- **Galleries** — `ProductGallerySeeder` tops every product up to three images, drawing extras from
  a pool belonging to its root category. It is idempotent (skips products that already have three),
  so it can be re-run over an existing database: `php artisan db:seed --class=ProductGallerySeeder`.
- **Policies** — one action, five documents at `/policies/{topic}` (`shipping`, `returns`,
  `size-guide`, `privacy`, `terms`) from `policyTopics()`; link them as `route('policies', 'terms')`.
  The size guide tabulates the size runs actually stocked.

**SEO & GEO** — page metadata is controller-owned, never written in a view.
- `App\Support\Seo` (scoped) carries one page's title / description / image / canonical /
  indexability / JSON-LD. Controllers configure it (`$this->seo->page(...)->image(...)->schema(...)`)
  and `partials/seo.blade.php` — included by `layouts/storefront` and fed by a view composer in
  `AppServiceProvider` — renders the lot. **There is no `@section('title')` on storefront pages**;
  adding one back does nothing. `layouts/auth` is separate and is `noindex, nofollow` wholesale.
- `App\Support\Schema` builds the JSON-LD nodes. Every page emits one `@graph` containing
  `OnlineStore` + `WebSite` (cross-referenced by `@id`) plus whatever the page is about:
  `Product`+`Offer`, `BreadcrumbList`, `CollectionPage`, `ItemList`, `FAQPage`, `WebPage`.
  **Do not add `aggregateRating` or `review`** — there is no reviews table, and fabricated rating
  counts are what earns a structured-data manual action. `Product->rating` is editorial, not reviews.
- **Canonicals.** Facet params (`size` `color` `min` `max` `sort`) canonicalise back to the clean
  category/edit URL *and* send `noindex, follow` — they are permutations of one product set.
  Pagination is the opposite: `?page=2` holds different products, so it self-canonicalises and stays
  indexable. The facets are deliberately **not** blocked in robots.txt: a blocked URL can never be
  fetched, so its `noindex` would never be read.
- `SeoController` serves `/robots.txt`, `/sitemap.xml` and `/llms.txt` as **routes**, all built from
  live data. `public/robots.txt` was deleted — a file in `public/` shadows the route, and a static
  file cannot name the sitemap at the current domain.
- **Every absolute URL derives from `APP_URL`.** No domain is hard-coded anywhere; setting
  `APP_URL=https://…` in production .env is all that canonicals, OG tags and the sitemap need.
- `config/seo.php` holds brand strings, the default description/image, currency and the `social`
  handles that become the Organization `sameAs`. Empty values render nothing.
- **GEO** — what generative engines quote is answer-shaped prose, so: `/llms.txt` describes the shop,
  its terms and its categories in plain language from live data; product pages carry a visible
  "Frequently asked" block (`StoreController::productFaqs()`, built from the piece's real sizes,
  colours and stock) and the policy pages expose their sections as `FAQPage`. **The schema is only
  legitimate because the answers render on the page** — never emit `FAQPage` for invisible content.
- Prices quoted in copy must come from `Cart::FREE_SHIPPING_THRESHOLD` / `Cart::STANDARD_SHIPPING`,
  not be retyped. (The policies page's "express $9.00" already collides with standard shipping's
  $9.00; that prose is stale, so the FAQ and llms.txt deliberately do not repeat the express figure.)

**Styling** — `resources/css/app.css` is the source of truth for design tokens:
- Brand palette as `--color-*` tokens (e.g. `ink`, `blush`, `tan`, `cream`, `muted`, `jade`) →
  use as Tailwind utilities like `bg-cream`, `text-blush`, `border-line-2`.
- Font tokens `--font-sans/serif/display` → `font-sans`, `font-serif`, `font-display`.
- Reusable component classes: `.tc-input`, `.tc-btn-dark`, `.tc-btn-outline`, `.tc-link`.
- `@source '../views'` ensures Blade class names are scanned.

## Conventions

- **Tailwind v4 spacing:** fractional utilities like `py-5.5` / `gap-6.5` are NOT valid — use
  arbitrary values instead (`py-[22px]`, `gap-[26px]`).
- Prefer the brand tokens and `.tc-*` component classes over ad-hoc colors so pages stay consistent.
- Reuse `partials/product-card` for any product grid rather than re-writing card markup.
- **Carousels** are CSS scroll-snap + a tiny vanilla helper in `resources/js/app.js`. Markup
  contract: a `[data-carousel]` wrapper containing a `[data-carousel-track]` (a
  `no-scrollbar flex snap-x snap-mandatory overflow-x-auto` row of `shrink-0 snap-start` items)
  plus optional `[data-carousel-prev]` / `[data-carousel-next]` buttons (style with `.tc-arrow`).
  The JS wires the arrows to `scrollBy` and disables them at the track extremes — no library.
  Used on the home page (Shop by Category, Featured Products, Deal of the Week).
- New pages: add a `StoreController` action (pass `active`/`bagCount`/`bagTotal`), a named route,
  and a `store/*.blade.php` that extends the storefront layout.
- Imagery comes from Unsplash via `img()`; keep the author/credit fields populated.

## Notes

- Auth controllers redirect to `/` after login (the default `/home` route was removed in favor of
  the storefront home).
- The legacy Bootstrap scaffolding (`welcome.blade.php`, `home.blade.php`, `layouts/app.blade.php`,
  `HomeController`) has been deleted — it was unrouted and referenced the removed `register` route.

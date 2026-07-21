# CLAUDE.md

Guidance for working in this repository.

## Project

**Trendy Closet by Leila Konsol** — a Laravel storefront (marketing/catalog UI) implemented from
the "Storefront Explorations" Claude Design doc. It is currently a **static, front-end-only
storefront**: pages render hard-coded demo data from `StoreController`. There is no database-backed
catalog, cart persistence, or checkout processing yet — forms and "add to bag" controls are visual.

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

`php artisan test` runs Pest against in-memory SQLite (see `phpunit.xml`) — it never touches the
dev MySQL database. The storefront pages themselves have no tests yet; `tests/Feature/
ProductAnalyticsTest.php` covers the analytics layer.

## Architecture

**Routing** — All storefront routes live in `routes/web.php` and point at `StoreController`.
Named routes: `home` `/`, `listing` `/women`, `product` `/product`, `cart` `/bag`,
`checkout` `/checkout`, `about`, `contact`, `policies`.

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
**Analytics** — there is **no `Cart` model or `carts` table**; the bag is not persisted. Product
engagement is tracked instead, split by shape:
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

**Controller** — `app/Http/Controllers/StoreController.php` holds all page data as PHP arrays
(translated from the design doc's JS). Key helpers:
- `img($id, $author, $slug, $w)` — builds an Unsplash image descriptor (`img` / `credit` /
  `credit_href`). Product/category records use `[...] + $this->img(...)` to merge fields.
- `services()`, `cartItems()` — shared data blocks.
- Every action passes `active` (nav highlight key), `bagCount`, and `bagTotal` to its view.

**Views** — `resources/views/`:
- `layouts/storefront.blade.php` — root layout: `<head>` (fonts + `@vite`), header, `@yield('content')`, footer. **Full-bleed** (no max-width wrapper); each section supplies its own horizontal padding (`px-8 md:px-16`).
- `partials/` — `header` (announcement bar, nav w/ WOMEN mega-menu + active-state highlighting via `$active`), `footer` (newsletter + columns), `product-card` (reusable card taking `$p` and optional height `$h`).
- `layouts/auth.blade.php` — standalone admin-auth layout (no storefront header/footer): editorial
  image panel on the left (`lg:` only) + form panel on the right. Auth pages fill the
  `eyebrow` / `heading` / `subheading` / `form` sections rather than `content`; `session('status')`
  is rendered by the layout.
- `partials/auth-field.blade.php` — label + `.tc-input` + inline `@error` message. Takes `name`,
  `label`, and optional `type` / `value` / `autocomplete` / `autofocus` / `placeholder`.
- `auth/` — `login`, `passwords/{email,reset,confirm}`, each `@extends('layouts.auth')`.
- `store/` — one Blade file per page (`home`, `listing`, `product`, `cart`, `checkout`, `about`, `contact`, `policies`), each `@extends('layouts.storefront')`.

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

<?php

namespace App\Services;

use App\Enums\SiteEventType;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteEvent;
use App\Support\TrafficSource;
use App\Support\Visitor;
use Illuminate\Http\Request;

/**
 * Records what happens on the shop. The site-wide sibling of
 * {@see ProductAnalytics}: same shape, same rule — this is the write side only,
 * and the reports read `site_events` back with plain aggregates.
 *
 * Every method swallows its own failures. Analytics must never be the reason a
 * shopper cannot check out, so a full disk or a locked table costs a data point
 * rather than a sale.
 */
class SiteAnalytics
{
    public function __construct(
        private readonly Visitor $visitor,
        private readonly Request $request,
    ) {}

    /**
     * A page opened. Called by the TrackPageView middleware, never by hand.
     */
    public function recordPageView(): void
    {
        $this->record(SiteEventType::PageView);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function record(SiteEventType $type, array $meta = [], ?Product $product = null, ?Order $order = null): void
    {
        $referrer = $this->request->headers->get('referer');

        try {
            SiteEvent::create([
                'name' => $type,
                'visitor_id' => $this->visitor->id,
                'session_id' => $this->sessionKey(),
                'path' => $this->path(),
                'referrer_host' => TrafficSource::host($referrer),
                'referrer' => $referrer ? mb_substr($referrer, 0, 512) : null,
                'product_id' => $product?->id,
                'order_id' => $order?->id,
                'meta' => $meta ?: null,
            ]);
        } catch (\Throwable) {
            // A missed data point is not worth an error page.
        }
    }

    /**
     * The path as it would be read in a report: query string dropped, so
     * `/shop?sort=newest` and `/shop` are one page rather than a long tail of
     * near-duplicates. The search term is kept as a `search` event instead.
     */
    private function path(): string
    {
        $path = '/'.ltrim($this->request->path(), '/');

        return mb_substr($path === '//' ? '/' : $path, 0, 255);
    }

    /**
     * A stable id for this sitting, hashed. The session is the right unit for
     * "a visit" — it expires the way a visit does — but the raw id is a
     * credential, so only a digest of it is stored.
     */
    private function sessionKey(): ?string
    {
        if (! $this->request->hasSession()) {
            return null;
        }

        return mb_substr(hash('sha256', $this->request->session()->getId()), 0, 32);
    }
}

<?php

namespace App\Support;

use App\Http\Middleware\TrackPageView;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Illuminate\Support\defer;

/**
 * The server half of the Meta pixel. Each event {@see Tracking} hands the
 * browser is also posted here under the same event id, so Meta keeps one and
 * still sees the shoppers whose browser blocked the pixel.
 *
 * Sent after the response has gone out, so a slow Graph API never slows a page.
 */
class MetaConversions
{
    public static function enabled(): bool
    {
        return Tracking::metaPixelId() !== null && filled(config('services.meta.capi_token'));
    }

    /**
     * @param  array{name: string, params: array<string, mixed>, id: string}  $meta
     */
    public static function send(array $meta, ?Order $order = null): void
    {
        if (! self::enabled()) {
            return;
        }

        $request = request();

        // A crawler runs no pixel, so its server copy would have nothing to pair with.
        if (TrackPageView::isBot((string) $request->userAgent())) {
            return;
        }

        $event = [
            'event_name' => $meta['name'],
            'event_time' => time(),
            'event_id' => $meta['id'],
            'event_source_url' => self::sourceUrl($request),
            'action_source' => 'website',
            'user_data' => self::userData($request) + ($order ? self::customer($order) : []),
            'custom_data' => (object) $meta['params'],
        ];

        defer(fn () => self::post($event));
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private static function post(array $event): void
    {
        $body = array_filter([
            'data' => [$event],
            'test_event_code' => config('services.meta.test_event_code') ?: null,
            'access_token' => config('services.meta.capi_token'),
        ]);

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/events',
            config('services.meta.graph_version'),
            Tracking::metaPixelId(),
        );

        try {
            $response = Http::timeout(5)->asJson()->post($url, $body);

            if ($response->failed()) {
                Log::warning('Meta Conversions API rejected an event', [
                    'event' => $event['event_name'],
                    'status' => $response->status(),
                    'error' => $response->json('error.message'),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Meta Conversions API unreachable', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Fetch requests (add to bag, a WhatsApp tap) answer on an endpoint, not the
     * page the shopper was on — the referer is that page.
     */
    private static function sourceUrl(Request $request): string
    {
        return $request->isMethod('GET')
            ? $request->fullUrl()
            : ($request->headers->get('referer') ?: $request->fullUrl());
    }

    /**
     * @return array<string, mixed>
     */
    private static function userData(Request $request): array
    {
        return array_filter([
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => $request->cookie('_fbp'),
            'fbc' => $request->cookie('_fbc') ?: self::fbcFromClick($request),
            'external_id' => app()->bound(Visitor::class)
                ? [hash('sha256', (string) app(Visitor::class))]
                : null,
        ]);
    }

    /**
     * The landing request of an ad click arrives before the pixel has had a
     * chance to write `_fbc`, so build it from the `fbclid` the same way it would.
     */
    private static function fbcFromClick(Request $request): ?string
    {
        $fbclid = $request->query('fbclid');

        if (! $fbclid && ($referer = $request->headers->get('referer'))) {
            parse_str((string) parse_url($referer, PHP_URL_QUERY), $query);
            $fbclid = $query['fbclid'] ?? null;
        }

        return is_string($fbclid) && $fbclid !== ''
            ? 'fb.1.'.(int) (microtime(true) * 1000).'.'.$fbclid
            : null;
    }

    /**
     * What the shopper typed at checkout, normalised and hashed the way Meta
     * matches it. Only the purchase carries this.
     *
     * @return array<string, array<int, string>>
     */
    private static function customer(Order $order): array
    {
        $hash = fn (?string $value) => ($value = trim((string) $value)) === '' ? null : [hash('sha256', $value)];

        [$first, $last] = array_pad(preg_split('/\s+/u', mb_strtolower(trim((string) $order->ship_name)), 2) ?: [], 2, null);

        $iso = collect(Countries::all())->search(fn (array $c) => $c['name'] === $order->ship_country);

        return array_filter([
            'ph' => $hash($order->ship_phone ? ltrim(Customer::normalizePhone($order->ship_phone), '+') : null),
            'em' => $hash(mb_strtolower((string) $order->email)),
            'fn' => $hash($first),
            'ln' => $hash($last),
            'ct' => $hash(preg_replace('/[^\p{L}]+/u', '', mb_strtolower((string) $order->ship_city))),
            'country' => $hash($iso !== false ? strtolower((string) $iso) : null),
        ]);
    }
}

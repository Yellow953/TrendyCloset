<?php

namespace App\Http\Middleware;

use App\Services\SiteAnalytics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs one `page_view` per storefront page actually shown to a person.
 *
 * The exclusions are the whole job here — a traffic report is only worth
 * reading if it counts shoppers, so this deliberately ignores the back office,
 * sign-in, the drawer fragments the page fetches for itself, anything that is
 * not a rendered HTML page, and the obvious crawlers. Recorded after the
 * response so a redirect or a 404 never counts as a visit.
 */
class TrackPageView
{
    /**
     * Paths that are not shopper-facing pages. Matched with `is()`, so `*` is a
     * wildcard.
     *
     * @var array<int, string>
     */
    private const IGNORED = [
        'admin', 'admin/*',
        'login', 'logout', 'password/*',
        'bag/drawer', 'favorites/drawer',
        'robots.txt', 'sitemap.xml', 'llms.txt',
        'up', 'storage/*', 'build/*',
    ];

    /**
     * Substrings of a user agent that mean "not a shopper". Cheap and
     * imperfect: it catches the crawlers that identify themselves, which is
     * most of the volume, and misses the ones that lie, which nothing catches.
     *
     * @var array<int, string>
     */
    private const BOTS = [
        'bot', 'crawler', 'spider', 'crawling', 'slurp', 'mediapartners',
        'facebookexternalhit', 'headlesschrome', 'phantomjs', 'lighthouse',
        'curl/', 'wget/', 'python-requests', 'go-http-client', 'axios/',
        'preview', 'monitor', 'pingdom', 'uptime', 'ahrefs', 'semrush',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // The response is already built by this point, so anything that goes
        // wrong here would turn a perfectly good page into a 500. Counting a
        // visit is never worth that.
        try {
            if ($this->shouldRecord($request, $response)) {
                app(SiteAnalytics::class)->recordPageView();
            }
        } catch (\Throwable) {
            // A missed page view is not worth an error page.
        }

        return $response;
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $request->is(...self::IGNORED)) {
            return false;
        }

        // Fetches the page makes for itself are not visits, and neither is a
        // browser speculatively pre-loading a link nobody has clicked yet.
        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        if ($request->headers->get('Purpose') === 'prefetch'
            || $request->headers->get('X-Purpose') === 'preview'
            || $request->headers->has('Sec-Purpose')) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $type = $response->headers->get('Content-Type', '');

        if ($type !== '' && ! str_contains($type, 'text/html')) {
            return false;
        }

        return ! $this->isBot((string) $request->userAgent());
    }

    private function isBot(string $agent): bool
    {
        if ($agent === '') {
            return true;
        }

        $agent = mb_strtolower($agent);

        foreach (self::BOTS as $needle) {
            if (str_contains($agent, $needle)) {
                return true;
            }
        }

        return false;
    }
}

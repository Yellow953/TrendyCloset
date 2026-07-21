<?php

namespace App\Http\Middleware;

use App\Support\Visitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives every browser a stable anonymous id so product analytics survive
 * session expiry. Deliberately not the session id: sessions die in hours, which
 * would make one returning shopper look like several visitors and would drop
 * their favourites overnight.
 */
class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $visitorId = $request->cookie(Visitor::COOKIE) ?: (string) Str::uuid();

        // Make it available to anything resolving Visitor during this request,
        // including the very first one, before the cookie round-trips.
        app()->instance(Visitor::class, new Visitor($visitorId));

        $response = $next($request);

        return $response->withCookie(
            cookie()->forever(Visitor::COOKIE, $visitorId, httpOnly: true)
        );
    }
}

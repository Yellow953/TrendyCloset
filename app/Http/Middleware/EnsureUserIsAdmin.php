<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to admins. Every authenticated user is back-office staff
 * (customers cannot sign in), so `auth` alone already gates the CRM — this is
 * the narrower gate for user management, coupons, and store settings.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return $next($request);
    }
}

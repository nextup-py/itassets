<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defense-in-depth alongside URL::forceScheme('https') in AppServiceProvider: that only
 * affects links Laravel *generates* (mail, QR codes, redirects), not an incoming request
 * that actually arrived over plain HTTP. CloudPanel's own per-site HTTPS-redirect toggle is
 * the primary enforcement (nginx redirecting before the request ever reaches PHP-FPM) — this
 * is the fallback for if that's ever off, not the main mechanism.
 */
class ForceHttpsInProduction
{
    public function handle(Request $request, Closure $next): Response
    {
        // Excludes the health-check route (/up, see bootstrap/app.php's ->withRouting()) —
        // an uptime monitor that doesn't follow redirects would otherwise read a 301 here
        // as "down" the moment it's ever pointed at a plain http:// URL.
        if (
            app()->environment('production')
            && (! $request->secure())
            && $request->isMethod('GET')
            && (! $request->is('up'))
        ) {
            // Built from $request's own host, not redirect()->secure() — that resolves
            // against the URL generator's bound request/config('app.url'), which can differ
            // from the host this particular request actually arrived on.
            return redirect()->to('https://'.$request->getHttpHost().$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}

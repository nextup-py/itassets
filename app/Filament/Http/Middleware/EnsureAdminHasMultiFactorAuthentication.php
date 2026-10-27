<?php

declare(strict_types=1);

namespace App\Filament\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

/**
 * Filament's own EnsureMultiFactorAuthenticationIsEnabled enforces 2FA for every
 * panel user once `->requiresMultiFactorAuthentication()` is on — there's no
 * built-in way to scope that to a role. Admins can touch everything (users,
 * roles, mail credentials, backups), so they're the only ones required to set
 * it up; Editor/Viewer may still enable it voluntarily from their profile, but
 * are never forced through the setup screen.
 */
class EnsureAdminHasMultiFactorAuthentication
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Filament::auth()->user();

        if (! $user?->hasRole('Admin')) {
            return $next($request);
        }

        foreach (Filament::getMultiFactorAuthenticationProviders() as $provider) {
            if ($provider->isEnabled($user)) {
                return $next($request);
            }
        }

        return redirect()->guest(Filament::getSetUpRequiredMultiFactorAuthenticationUrl());
    }
}

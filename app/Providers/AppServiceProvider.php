<?php

namespace App\Providers;

use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Services\MailConfigService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            $event->user->update(['last_login_at' => now()]);
        });

        // Spatie's Role/Permission models live outside app/Models, so Laravel's
        // policy auto-discovery convention won't find these — must register explicitly.
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        $this->app->make(MailConfigService::class)->apply();

        // Production only (not local/testing) — generated URLs (notification mail links,
        // the assignment PDF's QR, asset_tag QR, redirects) always use https://, regardless
        // of what scheme the current request arrived as. This matters specifically because
        // nginx (CloudPanel) terminates TLS and talks to PHP-FPM over FastCGI, not HTTP, so
        // there's no X-Forwarded-Proto header for TrustProxies to read in the first place —
        // forcing the scheme here is the actual fix, not a proxy-trust config.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\License;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Notifications\LicenseExpiryNotification;
use App\Notifications\MaintenanceAlertNotification;
use App\Notifications\WarrantyExpiryNotification;
use Illuminate\Console\Command;

class CheckExpirations extends Command
{
    protected $signature = 'notifications:check';
    protected $description = 'Check warranties, licenses and maintenance records for expirations and alerts';

    private const COOLDOWN_DAYS = 7;

    public function handle(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $admins = User::managers()->get();

        if ($admins->isEmpty()) {
            $this->warn('No users with Admin/Editor role found.');
            return;
        }

        $this->checkWarranties($admins);
        $this->checkLicenses($admins);
        $this->checkMaintenance($admins);

        $this->info('Notifications check completed.');
    }

    protected function checkWarranties(iterable $admins): void
    {
        $assets = Asset::whereNotNull('warranty_expiry_date')
            ->whereDate('warranty_expiry_date', '<=', now()->addDays(60))
            ->get();

        foreach ($assets as $asset) {
            $daysRemaining = now()->diffInDays($asset->warranty_expiry_date, false);

            foreach ($admins as $admin) {
                if ($this->recentlyNotified($admin, WarrantyExpiryNotification::class, 'asset_id', $asset->id)) {
                    continue;
                }

                (new WarrantyExpiryNotification($asset, (int) $daysRemaining))->sendToManager($admin);
            }
        }

        $this->info("Checked {$assets->count()} warranty expirations.");
    }

    protected function checkLicenses(iterable $admins): void
    {
        $licenses = License::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(60))
            ->get();

        foreach ($licenses as $license) {
            $daysRemaining = now()->diffInDays($license->expiry_date, false);

            foreach ($admins as $admin) {
                if ($this->recentlyNotified($admin, LicenseExpiryNotification::class, 'license_id', $license->id)) {
                    continue;
                }

                (new LicenseExpiryNotification($license, (int) $daysRemaining))->sendToManager($admin);
            }
        }

        $this->info("Checked {$licenses->count()} license expirations.");
    }

    protected function checkMaintenance(iterable $admins): void
    {
        $prolonged = MaintenanceRecord::whereIn('status', ['in_progress', 'pending'])
            ->whereDate('started_at', '<=', now()->subDays(7))
            ->get();

        foreach ($prolonged as $record) {
            foreach ($admins as $admin) {
                if ($this->recentlyNotified($admin, MaintenanceAlertNotification::class, 'maintenance_id', $record->id)) {
                    continue;
                }

                (new MaintenanceAlertNotification($record, 'prolonged'))->sendToManager($admin);
            }
        }

        $this->info("Checked {$prolonged->count()} prolonged maintenance records.");
    }

    protected function recentlyNotified(User $admin, string $notificationClass, string $dataKey, int|string $id): bool
    {
        return $admin->notifications()
            ->where('type', $notificationClass)
            ->where("data->{$dataKey}", $id)
            ->where('created_at', '>=', now()->subDays(self::COOLDOWN_DAYS))
            ->exists();
    }
}

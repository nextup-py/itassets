<?php

use App\Filament\Widgets\RecentNotificationsWidget;
use App\Models\Asset;
use App\Notifications\WarrantyExpiryNotification;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = loginAsAdmin();
});

it('lists the current user\'s recent notifications', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => now()->addDays(10)]);
    (new WarrantyExpiryNotification($asset, 10))->sendToManager($this->admin);

    Livewire::test(RecentNotificationsWidget::class)
        ->assertSeeText('Garantía por vencer');
});

it('does not list the plain-database duplicate written alongside the Filament copy', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => now()->addDays(10)]);
    (new WarrantyExpiryNotification($asset, 10))->sendToManager($this->admin);

    expect($this->admin->notifications()->count())->toBe(2);

    Livewire::test(RecentNotificationsWidget::class)
        ->assertCountTableRecords(1);
});

it('marks a notification as read', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => now()->addDays(10)]);
    (new WarrantyExpiryNotification($asset, 10))->sendToManager($this->admin);
    $notification = $this->admin->notifications()->where('data->format', 'filament')->first();

    expect($notification->read_at)->toBeNull();

    Livewire::test(RecentNotificationsWidget::class)
        ->callTableAction('markAsRead', $notification);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('shows at most 10 notifications, prioritizing the most recent', function () {
    foreach (range(1, 12) as $i) {
        \Filament\Notifications\Notification::make()
            ->title("Notificación {$i}")
            ->sendToDatabase($this->admin);

        $this->admin->notifications()->latest()->first()
            ->update(['created_at' => now()->subMinutes(12 - $i)]);
    }

    // Query DatabaseNotification directly: the notifiable's notifications() relation
    // bakes in its own ->orderBy('created_at', 'desc'), which a chained ->oldest()
    // can't override (both sort the same column, so the first clause wins).
    $oldest = DatabaseNotification::query()->where('notifiable_id', $this->admin->id)
        ->reorder('created_at', 'asc')->limit(2)->get();
    $newest = DatabaseNotification::query()->where('notifiable_id', $this->admin->id)
        ->reorder('created_at', 'desc')->limit(10)->get();

    Livewire::test(RecentNotificationsWidget::class)
        ->assertCanSeeTableRecords($newest)
        ->assertCanNotSeeTableRecords($oldest);
});

it('shows the title for Filament-format notifications', function () {
    \Filament\Notifications\Notification::make()
        ->title('Activo asignado: IT-0001 Test')
        ->sendToDatabase($this->admin);

    Livewire::test(RecentNotificationsWidget::class)
        ->assertSeeText('Activo asignado: IT-0001 Test');
});

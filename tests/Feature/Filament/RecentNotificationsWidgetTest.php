<?php

use App\Filament\Widgets\RecentNotificationsWidget;
use App\Models\Asset;
use App\Notifications\WarrantyExpiryNotification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = loginAsAdmin();
});

it('lists the current user\'s recent notifications', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => now()->addDays(10)]);
    $this->admin->notify(new WarrantyExpiryNotification($asset, 10));

    Livewire::test(RecentNotificationsWidget::class)
        ->assertSeeText('Garantía por vencer');
});

it('marks a notification as read', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => now()->addDays(10)]);
    $this->admin->notify(new WarrantyExpiryNotification($asset, 10));
    $notification = $this->admin->notifications()->first();

    expect($notification->read_at)->toBeNull();

    Livewire::test(RecentNotificationsWidget::class)
        ->callTableAction('markAsRead', $notification);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('shows the title for Filament-format notifications', function () {
    \Filament\Notifications\Notification::make()
        ->title('Activo asignado: IT-0001 Test')
        ->sendToDatabase($this->admin);

    Livewire::test(RecentNotificationsWidget::class)
        ->assertSeeText('Activo asignado: IT-0001 Test');
});

<?php

namespace App\Notifications\Concerns;

use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;

trait SendsToManagers
{
    abstract public function toFilament(): FilamentNotification;

    public function sendToManager(User $manager): void
    {
        $manager->notify($this);
        $this->toFilament()->sendToDatabase($manager);
    }
}

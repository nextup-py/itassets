<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected bool $recordHadAdminRoleBeforeSave = false;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleActive')
                ->label(fn (User $record): string => $record->is_active ? 'Desactivar' : 'Activar')
                ->icon(fn (User $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                ->color(fn (User $record): string => $record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->visible(fn (): bool => Auth::user()->can('delete_user'))
                ->action(function (Action $action, User $record): void {
                    if (! $record->is_active) {
                        $record->update(['is_active' => true]);

                        return;
                    }

                    if ($record->id === Auth::id()) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede desactivar')
                            ->body('No podés desactivar tu propia cuenta.')
                            ->send();

                        $action->halt();

                        return;
                    }

                    if ($record->hasRole('Admin') && User::role('Admin')->where('is_active', true)->count() <= 1) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede desactivar')
                            ->body('No se puede desactivar al último administrador activo del sistema.')
                            ->send();

                        $action->halt();

                        return;
                    }

                    $record->update(['is_active' => false]);
                }),

            // Recovery codes exist specifically because there's no support staff to
            // fall back on if an Admin loses their authenticator device (see
            // AdminPanelProvider) -- but nothing covered losing the recovery codes
            // too. Without this, that's an unrecoverable lockout short of editing
            // the database directly on the server.
            Action::make('resetTwoFactor')
                ->label('Restablecer 2FA')
                ->icon('heroicon-o-shield-exclamation')
                ->color('warning')
                ->authorize('update_user')
                ->requiresConfirmation()
                ->modalDescription('El usuario deberá configurar la autenticación de dos factores nuevamente en su próximo inicio de sesión.')
                ->visible(fn (User $record): bool => $record->getAppAuthenticationSecret() !== null)
                ->action(function (User $record): void {
                    $record->saveAppAuthenticationSecret(null);
                    $record->saveAppAuthenticationRecoveryCodes(null);

                    Notification::make()
                        ->success()
                        ->title('2FA restablecido')
                        ->body('El usuario deberá configurarlo nuevamente en su próximo inicio de sesión.')
                        ->send();
                }),
        ];
    }

    protected function beforeSave(): void
    {
        $this->recordHadAdminRoleBeforeSave = $this->record->hasRole('Admin');

        if (! $this->recordHadAdminRoleBeforeSave) {
            return;
        }

        $adminRoleId = Role::where('name', 'Admin')->value('id');
        $submittedRoleIds = $this->data['roles'] ?? [];
        $keepsAdmin = $adminRoleId && in_array($adminRoleId, $submittedRoleIds, false);

        if ($keepsAdmin) {
            return;
        }

        if ($this->record->id === Auth::id()) {
            Notification::make()
                ->danger()
                ->title('No se puede guardar')
                ->body('No podés quitarte el rol de Admin a vos mismo.')
                ->send();

            $this->halt();

            return;
        }

        if (User::role('Admin')->where('is_active', true)->count() <= 1) {
            Notification::make()
                ->danger()
                ->title('No se puede guardar')
                ->body('No se puede quitar el rol de Admin al último administrador del sistema.')
                ->send();

            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        if (Auth::user()->hasRole('Admin')) {
            return;
        }

        // Non-admins can never grant or revoke the Admin role, no matter what
        // was submitted — restore whatever the record's Admin status was
        // before this save, regardless of what the roles relationship synced.
        if ($this->recordHadAdminRoleBeforeSave) {
            $this->record->assignRole('Admin');
        } else {
            $this->record->removeRole('Admin');
        }
    }
}

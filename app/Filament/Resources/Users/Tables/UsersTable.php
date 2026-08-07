<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('primary')
                    ->separator(',')
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('last_login_at')
                    ->label('Último acceso')
                    ->dateTime(current_datetime_format())
                    ->placeholder('Nunca')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date(current_date_format())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Rol')
                    ->relationship('roles', 'name'),

                TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('deactivate')
                        ->label('Desactivar')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => Auth::user()->can('delete_user'))
                        ->action(function (BulkAction $action, Collection $records): void {
                            if ($records->contains('id', Auth::id())) {
                                Notification::make()
                                    ->danger()
                                    ->title('No se puede desactivar')
                                    ->body('No podés incluir tu propia cuenta en una desactivación masiva.')
                                    ->send();

                                $action->halt();

                                return;
                            }

                            $activeAdminsBeingDeactivated = $records->filter(
                                fn (User $record) => $record->is_active && $record->hasRole('Admin')
                            )->count();

                            $remainingActiveAdmins = User::role('Admin')->where('is_active', true)->count()
                                - $activeAdminsBeingDeactivated;

                            if ($remainingActiveAdmins < 1) {
                                Notification::make()
                                    ->danger()
                                    ->title('No se puede desactivar')
                                    ->body('Esta selección desactivaría a todos los administradores activos del sistema.')
                                    ->send();

                                $action->halt();

                                return;
                            }

                            $records->each(fn (User $record) => $record->update(['is_active' => false]));
                        }),
                ]),
            ])
            ->defaultSort('name');
    }
}

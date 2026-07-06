<?php

namespace App\Filament\Resources\Licenses\Tables;

use App\Models\License;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LicensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(License::query()->withCount('activeAssignments'))
            ->columns([
                TextColumn::make('product_name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('license_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => License::TYPES[$state] ?? $state),

                TextColumn::make('seats')
                    ->label('Seats (usados / total)')
                    ->state(fn (License $record): string => $record->usedSeats() . ' / ' . $record->total_seats)
                    ->badge()
                    ->color(fn (License $record): string => $record->availableSeats() === 0 ? 'danger' : 'success'),

                TextColumn::make('expiry_date')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->placeholder('Sin vencimiento')
                    ->color(fn (License $record): ?string => $record->expiry_date?->isPast() ? 'danger'
                        : ($record->expiry_date?->diffInDays(now()) <= 60 ? 'warning' : null))
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('purchase_price')
                    ->label('Precio')
                    ->formatStateUsing(fn ($state) => is_null($state) ? '—' : \format_gs($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('license_type')
                    ->label('Tipo')
                    ->options(License::TYPES),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('product_name');
    }
}

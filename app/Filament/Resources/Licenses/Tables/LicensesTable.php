<?php

namespace App\Filament\Resources\Licenses\Tables;

use App\Models\License;
use App\Models\Supplier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->label('Puestos (usados / total)')
                    ->state(fn (License $record): string => $record->usedSeats() . ' / ' . $record->total_seats)
                    ->badge()
                    ->color(fn (License $record): string => $record->availableSeats() === 0 ? 'danger' : 'success'),

                TextColumn::make('expiry_date')
                    ->label('Vence')
                    ->date(current_date_format())
                    ->placeholder('Sin vencimiento')
                    ->color(fn (License $record): ?string => $record->expiry_date?->isPast() ? 'danger'
                        : ($record->expiry_date?->diffInDays(now()) <= 60 ? 'warning' : null))
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('purchase_price')
                    ->label('Precio')
                    ->formatStateUsing(fn ($state, License $record) => is_null($state) ? '—' : \format_currency($state, $record->currency))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('license_type')
                    ->label('Tipo')
                    ->options(License::TYPES),

                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->options(Supplier::pluck('name', 'id')),

                Filter::make('expiry_status')
                    ->label('Estado de vencimiento')
                    ->form([
                        Select::make('value')
                            ->label('Estado')
                            ->options([
                                'expired' => 'Vencidas',
                                'expiring_soon' => 'Por vencer (≤60 días)',
                                'valid' => 'Vigentes',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'expired' => $query->whereDate('expiry_date', '<', now()),
                            'expiring_soon' => $query->whereBetween('expiry_date', [now(), now()->addDays(60)]),
                            'valid' => $query->where(function ($q) {
                                $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>', now()->addDays(60));
                            }),
                            default => $query,
                        };
                    })
                    ->indicateUsing(fn (array $data): ?string => match ($data['value'] ?? null) {
                        'expired' => 'Vencidas',
                        'expiring_soon' => 'Por vencer (≤60 días)',
                        'valid' => 'Vigentes',
                        default => null,
                    }),
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

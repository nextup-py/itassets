<?php

namespace App\Filament\Resources\ServiceContracts\Tables;

use App\Models\ServiceContract;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contract_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ServiceContract::TYPES[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('expiry_date')
                    ->label('Vence')
                    ->date(current_date_format())
                    ->placeholder('Sin vencimiento')
                    ->color(fn (ServiceContract $record): ?string => $record->expiry_date?->isPast() ? 'danger'
                        : ($record->expiry_date?->diffInDays(now()) <= 60 ? 'warning' : null))
                    ->sortable(),

                TextColumn::make('assets_count')
                    ->label('Activos')
                    ->counts('assets')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('cost')
                    ->label('Costo')
                    ->formatStateUsing(fn ($state, ServiceContract $record) => is_null($state) ? '—' : \format_currency($state, $record->currency))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('contract_type')
                    ->label('Tipo')
                    ->options(ServiceContract::TYPES),

                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('expiry_status')
                    ->label('Estado de vencimiento')
                    ->form([
                        Select::make('value')
                            ->label('Estado')
                            ->options([
                                'expired' => 'Vencidos',
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
                        'expired' => 'Vencidos',
                        'expiring_soon' => 'Por vencer (≤60 días)',
                        'valid' => 'Vigentes',
                        default => null,
                    }),
            ])
            ->recordActions([
                Action::make('renew')
                    ->label('Renovar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->authorize('update_service_contract')
                    ->visible(fn (ServiceContract $record): bool => $record->expiry_date
                        && $record->expiry_date->diffInDays(now(), false) >= -60)
                    ->form([
                        DatePicker::make('expiry_date')
                            ->label('Nueva fecha de vencimiento')
                            ->required()
                            ->default(fn (ServiceContract $record) => $record->expiry_date)
                            ->displayFormat(current_date_format()),
                    ])
                    ->action(function (ServiceContract $record, array $data): void {
                        $record->update($data);

                        Notification::make()->success()->title('Contrato renovado correctamente')->send();
                    }),
            ])
            ->defaultSort('name');
    }
}

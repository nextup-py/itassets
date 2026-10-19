<?php

namespace App\Filament\Resources\ServiceContracts\Schemas;

use App\Models\ServiceContract;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información del contrato')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->columnSpanFull(),

                        TextEntry::make('contract_type')
                            ->label('Tipo')
                            ->badge()
                            ->formatStateUsing(fn (ServiceContract $record): string => $record->getTypeLabel()),

                        TextEntry::make('supplier.name')
                            ->label('Proveedor')
                            ->placeholder('—'),

                        TextEntry::make('sla')
                            ->label('SLA / Nivel de servicio')
                            ->placeholder('—'),

                        TextEntry::make('assets.name')
                            ->label('Activos cubiertos')
                            ->badge()
                            ->placeholder('Sin activos vinculados')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->label('Notas')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Vigencia y facturación')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        TextEntry::make('start_date')
                            ->label('Inicio')
                            ->date(current_date_format())
                            ->placeholder('—'),

                        TextEntry::make('expiry_date')
                            ->label('Vencimiento')
                            ->date(current_date_format())
                            ->placeholder('Sin vencimiento')
                            ->color(fn (ServiceContract $record): ?string => $record->expiry_date?->isPast() ? 'danger'
                                : ($record->expiry_date?->diffInDays(now()) <= 60 ? 'warning' : null)),

                        IconEntry::make('auto_renew')
                            ->label('Renovación automática')
                            ->boolean(),

                        TextEntry::make('billing_cycle')
                            ->label('Facturación')
                            ->placeholder('—')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? ServiceContract::BILLING_CYCLES[$state] ?? $state : null),

                        TextEntry::make('cost')
                            ->label('Costo')
                            ->formatStateUsing(fn ($state, ServiceContract $record) => is_null($state) ? '—' : \format_currency($state, $record->currency)),
                    ])
                    ->columns(3),
            ]);
    }
}

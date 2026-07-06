<?php

namespace App\Filament\Resources\Licenses\Schemas;

use App\Models\License;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LicenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la licencia')
                    ->icon('heroicon-o-key')
                    ->schema([
                        TextEntry::make('product_name')
                            ->label('Producto / Software')
                            ->columnSpan(2),

                        TextEntry::make('license_type')
                            ->label('Tipo')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => License::TYPES[$state] ?? $state),

                        TextEntry::make('seats')
                            ->label('Seats (usados / total)')
                            ->state(fn (License $record): string => $record->usedSeats() . ' / ' . $record->total_seats)
                            ->badge()
                            ->color(fn (License $record): string => $record->availableSeats() === 0 ? 'danger' : 'success'),

                        TextEntry::make('license_key')
                            ->label('Clave / Número de licencia')
                            ->placeholder('—')
                            ->copyable()
                            ->columnSpanFull(),

                        TextEntry::make('purchase_date')
                            ->label('Fecha de compra')
                            ->date('d/m/Y')
                            ->placeholder('—'),

                        TextEntry::make('expiry_date')
                            ->label('Vencimiento')
                            ->date('d/m/Y')
                            ->placeholder('Sin vencimiento')
                            ->color(fn (License $record): ?string => $record->expiry_date?->isPast() ? 'danger'
                                : ($record->expiry_date?->diffInDays(now()) <= 60 ? 'warning' : null)),

                        TextEntry::make('purchase_price')
                            ->label('Precio de compra')
                            ->formatStateUsing(fn ($state) => is_null($state) ? '—' : \format_gs($state)),

                        TextEntry::make('supplier.name')
                            ->label('Proveedor')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Notas')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->placeholder('Sin notas')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

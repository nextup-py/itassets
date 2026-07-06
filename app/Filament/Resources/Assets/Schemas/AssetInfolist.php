<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\Asset;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Información general ──────────────────────────────────────
                Section::make('Información general')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('asset_tag')
                            ->label('Código'),

                        TextEntry::make('name')
                            ->label('Nombre / Descripción')
                            ->columnSpan(2),

                        TextEntry::make('category.name')
                            ->label('Categoría'),

                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (Asset $record): string => $record->getStatusLabel())
                            ->color(fn (Asset $record): string => $record->getStatusBadgeColor()),

                        TextEntry::make('condition')
                            ->label('Condición')
                            ->formatStateUsing(fn (?string $state): string => $state ? (Asset::CONDITIONS[$state] ?? $state) : '—'),

                        TextEntry::make('brand')
                            ->label('Marca')
                            ->placeholder('—'),

                        TextEntry::make('model')
                            ->label('Modelo')
                            ->placeholder('—'),

                        TextEntry::make('serial_number')
                            ->label('Número de serie')
                            ->placeholder('—')
                            ->copyable(),

                        ImageEntry::make('photo')
                            ->label('Fotografía')
                            ->disk('public')
                            ->height(160)
                            ->columnSpanFull()
                            ->hidden(fn ($record) => empty($record->photo)),

                        TextEntry::make('notes')
                            ->label('Notas')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                // ── Adquisición ───────────────────────────────────────────────
                Section::make('Adquisición')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        TextEntry::make('purchase_date')
                            ->label('Fecha de compra')
                            ->date('d/m/Y')
                            ->placeholder('—'),

                        TextEntry::make('purchase_price')
                            ->label('Precio de compra')
                            ->formatStateUsing(fn ($state) => is_null($state) ? '—' : \format_gs($state)),

                        TextEntry::make('supplier.name')
                            ->label('Proveedor')
                            ->placeholder('—'),

                        TextEntry::make('location.name')
                            ->label('Ubicación')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                // ── Garantía ──────────────────────────────────────────────────
                Section::make('Garantía')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextEntry::make('warranty_expiry_date')
                            ->label('Vence')
                            ->date('d/m/Y')
                            ->placeholder('Sin garantía registrada')
                            ->color(fn ($record) => $record?->warranty_expiry_date?->isPast() ? 'danger' : 'success'),

                        TextEntry::make('warrantySupplier.name')
                            ->label('Proveedor de garantía')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

            ]);
    }
}

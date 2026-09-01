<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\Asset;
use App\Support\AssetQrCode;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
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

                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (Asset $record): string => $record->getStatusLabel())
                            ->color(fn (Asset $record): string => $record->getStatusBadgeColor()),

                        TextEntry::make('condition')
                            ->label('Condición')
                            ->formatStateUsing(fn (?string $state): string => $state ? (Asset::CONDITIONS[$state] ?? $state) : '—'),

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
                            ->date(current_date_format())
                            ->placeholder('—'),

                        TextEntry::make('purchase_price')
                            ->label('Precio de compra')
                            ->formatStateUsing(fn ($state, Asset $record) => is_null($state) ? '—' : \format_currency($state, $record->currency)),

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
                            ->date(current_date_format())
                            ->placeholder('Sin garantía registrada')
                            ->color(fn ($record) => $record?->warranty_expiry_date?->isPast() ? 'danger' : 'success'),

                        TextEntry::make('warrantySupplier.name')
                            ->label('Proveedor de garantía')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                // ── Código QR ─────────────────────────────────────────────────
                Section::make('Código QR')
                    ->description('Para imprimir y pegar en el activo — al escanearlo, se abre una ficha pública con los datos básicos.')
                    ->icon('heroicon-o-qr-code')
                    ->headerActions([
                        Action::make('downloadQr')
                            ->label('Descargar QR')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('gray')
                            ->url(fn (Asset $record): string => route('assets.qr-image', $record), shouldOpenInNewTab: true),

                        Action::make('printQrLabel')
                            ->label('Imprimir etiqueta')
                            ->icon('heroicon-o-printer')
                            ->color('gray')
                            ->authorize('export_report')
                            ->url(function (Asset $record): string {
                                $token = Str::random(32);
                                Cache::put("qr_sheet.{$token}", [$record->id], now()->addMinutes(5));

                                return route('assets.qr-sheet', $token);
                            }, shouldOpenInNewTab: true),
                    ])
                    ->schema([
                        ImageEntry::make('qr')
                            ->hiddenLabel()
                            ->state(fn (Asset $record): string => AssetQrCode::dataUri($record))
                            ->height(160)
                            ->width(160)
                            ->alignCenter()
                            ->columnSpanFull(),

                        TextEntry::make('qr_url')
                            ->hiddenLabel()
                            ->state(fn (Asset $record): string => AssetQrCode::url($record))
                            ->copyable()
                            ->copyMessage('Enlace copiado')
                            ->color('gray')
                            ->size('sm')
                            ->alignCenter()
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'text-center']),
                    ]),

            ]);
    }
}

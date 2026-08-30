<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información general')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Razón Social / Nombre')
                            ->columnSpanFull(),

                        TextEntry::make('contact_name')
                            ->label('Contacto')
                            ->placeholder('—'),

                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->placeholder('—')
                            ->copyable(),

                        TextEntry::make('email')
                            ->label('Correo electrónico')
                            ->placeholder('—')
                            ->copyable(),

                        TextEntry::make('website')
                            ->label('Sitio web')
                            ->placeholder('—')
                            ->url(fn ($record) => $record->website)
                            ->openUrlInNewTab(),
                    ])
                    ->columns(2),

                Section::make('Estadísticas')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        TextEntry::make('assets_count')
                            ->label('Activos provistos')
                            ->state(fn ($record) => $record->assets()->count())
                            ->badge(),

                        TextEntry::make('licenses_count')
                            ->label('Licencias provistas')
                            ->state(fn ($record) => $record->licenses()->count())
                            ->badge(),

                        TextEntry::make('maintenance_records_count')
                            ->label('Mantenimientos realizados')
                            ->state(fn ($record) => $record->maintenanceRecords()->count())
                            ->badge(),
                    ])
                    ->columns(3),

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

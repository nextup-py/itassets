<?php

namespace App\Filament\Resources\MaintenanceRecords\Schemas;

use App\Models\MaintenanceRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del mantenimiento')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        TextEntry::make('asset.asset_tag')
                            ->label('Código'),

                        TextEntry::make('asset.name')
                            ->label('Activo')
                            ->columnSpan(2),

                        TextEntry::make('type')
                            ->label('Tipo')
                            ->badge()
                            ->formatStateUsing(fn (MaintenanceRecord $record): string => $record->getTypeLabel()),

                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (MaintenanceRecord $record): string => $record->getStatusLabel())
                            ->color(fn (MaintenanceRecord $record): string => $record->getStatusBadgeColor()),

                        TextEntry::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Servicio')
                    ->icon('heroicon-o-wrench')
                    ->schema([
                        TextEntry::make('technician')
                            ->label('Técnico responsable')
                            ->placeholder('—'),

                        TextEntry::make('supplier.name')
                            ->label('Proveedor de servicio')
                            ->placeholder('—'),

                        TextEntry::make('cost')
                            ->label('Costo')
                            ->formatStateUsing(fn ($state) => is_null($state) ? '—' : \format_currency($state)),

                        TextEntry::make('started_at')
                            ->label('Fecha de inicio')
                            ->date('d/m/Y'),

                        TextEntry::make('completed_at')
                            ->label('Fecha de término')
                            ->date('d/m/Y')
                            ->placeholder('En curso'),
                    ])
                    ->columns(2),

                Section::make('Resolución')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        TextEntry::make('resolution')
                            ->label('Diagnóstico / Resolución')
                            ->placeholder('Pendiente')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->label('Notas adicionales')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

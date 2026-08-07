<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use App\Filament\Resources\MaintenanceRecords\MaintenanceRecordResource;
use App\Models\MaintenanceRecord;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MaintenanceRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenanceRecords';

    protected static ?string $title = 'Mantenimientos';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('asset.asset_tag')
                    ->label('Código')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (MaintenanceRecord $record): string => $record->getTypeLabel()),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (MaintenanceRecord $record): string => $record->getStatusLabel())
                    ->color(fn (MaintenanceRecord $record): string => $record->getStatusBadgeColor()),

                TextColumn::make('started_at')
                    ->label('Inicio')
                    ->date(current_date_format()),

                TextColumn::make('completed_at')
                    ->label('Término')
                    ->date(current_date_format())
                    ->placeholder('En curso'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (MaintenanceRecord $record) => MaintenanceRecordResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('started_at', 'desc');
    }
}

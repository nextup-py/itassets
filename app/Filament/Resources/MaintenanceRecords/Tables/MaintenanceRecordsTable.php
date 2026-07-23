<?php

namespace App\Filament\Resources\MaintenanceRecords\Tables;

use App\Models\MaintenanceRecord;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset.asset_tag')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('asset.name')
                    ->label('Activo')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (MaintenanceRecord $record): string => $record->getTypeLabel()),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (MaintenanceRecord $record): string => $record->getStatusLabel())
                    ->color(fn (MaintenanceRecord $record): string => $record->getStatusBadgeColor())
                    ->sortable(),

                TextColumn::make('technician')
                    ->label('Técnico')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('cost')
                    ->label('Costo')
                    ->formatStateUsing(fn ($state) => is_null($state) ? '—' : \format_currency($state))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('started_at')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Término')
                    ->date('d/m/Y')
                    ->placeholder('En curso')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(MaintenanceRecord::TYPES),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(MaintenanceRecord::STATUSES),
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
            ->defaultSort('started_at', 'desc');
    }
}

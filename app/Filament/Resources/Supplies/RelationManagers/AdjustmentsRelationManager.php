<?php

namespace App\Filament\Resources\Supplies\RelationManagers;

use App\Models\SupplyAdjustment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdjustmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'adjustments';

    protected static ?string $title = 'Bajas / Ajustes de inventario';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('quantity')
                    ->label('Cantidad'),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->badge()
                    ->formatStateUsing(fn (SupplyAdjustment $record): string => $record->getReasonLabel())
                    ->color('danger'),

                TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(60)
                    ->placeholder('—'),

                TextColumn::make('creator.name')
                    ->label('Usuario')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime(current_datetime_format())
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('reason')
                    ->label('Motivo')
                    ->options(SupplyAdjustment::REASONS),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}

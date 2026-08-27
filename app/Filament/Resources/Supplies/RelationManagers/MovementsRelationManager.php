<?php

namespace App\Filament\Resources\Supplies\RelationManagers;

use App\Models\SupplyMovement;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Movimientos (entregas / reemplazos)';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (SupplyMovement $record): string => $record->getTypeLabel())
                    ->color(fn (string $state): string => $state === 'reemplazo' ? 'warning' : 'info'),

                TextColumn::make('quantity')
                    ->label('Cantidad'),

                TextColumn::make('recipient')
                    ->label('Destino')
                    ->state(fn (SupplyMovement $record): string => $record->getRecipientLabel()),

                TextColumn::make('receivedSupply.name')
                    ->label('Insumo recibido')
                    ->placeholder('—'),

                TextColumn::make('performed_at')
                    ->label('Fecha')
                    ->date(current_date_format())
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Registrado por')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(SupplyMovement::TYPES),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('performed_at', 'desc');
    }
}

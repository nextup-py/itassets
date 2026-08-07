<?php

namespace App\Filament\Resources\Locations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('building')
                    ->label('Edificio')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('floor')
                    ->label('Piso')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('room')
                    ->label('Sala / Área')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('assets_count')
                    ->label('Activos')
                    ->counts('assets')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date(current_date_format())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}

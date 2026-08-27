<?php

namespace App\Filament\Resources\Supplies\Tables;

use App\Models\AssetCategory;
use App\Models\Location;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SuppliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('quantity_available')
                    ->label('Disponible')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('quantity_damaged')
                    ->label('Dañado / Revisión')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray')
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label('Ubicación')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date(current_date_format())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('asset_category_id')
                    ->label('Categoría')
                    ->options(AssetCategory::pluck('name', 'id')),

                SelectFilter::make('location_id')
                    ->label('Ubicación')
                    ->options(Location::pluck('name', 'id')),
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
            ->defaultSort('name');
    }
}

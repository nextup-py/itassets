<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Supplier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->width(40)
                    ->defaultImageUrl(null)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('asset_tag')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('serial_number')
                    ->label('N/S')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (Asset $record): string => $record->getStatusLabel())
                    ->color(fn (Asset $record): string => $record->getStatusBadgeColor())
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label('Ubicación')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('warranty_expiry_date')
                    ->label('Garantía')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->color(fn ($record) => $record?->warranty_expiry_date?->isPast() ? 'danger' : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('asset_category_id')
                    ->label('Categoría')
                    ->options(AssetCategory::pluck('name', 'id')),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Asset::STATUSES),

                SelectFilter::make('location_id')
                    ->label('Ubicación')
                    ->options(Location::pluck('name', 'id')),

                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->options(Supplier::pluck('name', 'id')),
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
            ->defaultSort('asset_tag');
    }
}

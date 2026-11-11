<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use App\Filament\Resources\Supplies\SupplyResource;
use App\Models\Supply;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuppliesRelationManager extends RelationManager
{
    protected static string $relationship = 'supplies';

    protected static ?string $title = 'Insumos';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity_available')
                    ->label('Disponible')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),

                TextColumn::make('quantity_damaged')
                    ->label('Dañado / Revisión')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),

                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Supply $record) => SupplyResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('name');
    }
}

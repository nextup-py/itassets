<?php

namespace App\Filament\Resources\Shared;

use App\Filament\Resources\Assets\AssetResource;
use App\Models\Asset;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Shared "Activos" tab for AssetCategories/Locations/Suppliers: identical
 * except for the fourth column, which shows whichever of category/location
 * isn't already implied by the owner record (redundant to repeat it there).
 */
abstract class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    protected static ?string $title = 'Activos';

    abstract protected static function extraColumn(): TextColumn;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('asset_tag')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (Asset $record): string => $record->getStatusLabel())
                    ->color(fn (Asset $record): string => $record->getStatusBadgeColor()),

                static::extraColumn(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Asset $record) => AssetResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('asset_tag');
    }
}

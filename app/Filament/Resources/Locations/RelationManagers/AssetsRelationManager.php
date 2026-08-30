<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use App\Filament\Resources\Shared\AssetsRelationManager as SharedAssetsRelationManager;
use Filament\Tables\Columns\TextColumn;

class AssetsRelationManager extends SharedAssetsRelationManager
{
    protected static function extraColumn(): TextColumn
    {
        return TextColumn::make('category.name')
            ->label('Categoría')
            ->placeholder('—');
    }
}

<?php

namespace App\Filament\Resources\AssetCategories\Schemas;

use App\Models\AssetCategory;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AssetCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->unique(AssetCategory::class, 'name', ignoreRecord: true)
                    ->columnSpan(1),

                Toggle::make('is_mobile')
                    ->label('Dispositivo móvil')
                    ->helperText('Los activos de esta categoría muestran campos de IMEI, línea y operador.')
                    ->columnSpan(1),

                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}

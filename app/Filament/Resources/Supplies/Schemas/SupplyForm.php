<?php

namespace App\Filament\Resources\Supplies\Schemas;

use App\Filament\Resources\AssetCategories\Schemas\AssetCategoryForm;
use App\Filament\Resources\Locations\Schemas\LocationForm;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información general')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('asset_category_id')
                            ->label('Categoría')
                            ->required()
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => AssetCategoryForm::configure($schema))
                            ->columnSpan(1),

                        TextInput::make('quantity_available')
                            ->label('Cantidad disponible')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->columnSpan(1),

                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => SupplierForm::configure($schema))
                            ->columnSpan(1),

                        Select::make('location_id')
                            ->label('Ubicación')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => LocationForm::configure($schema))
                            ->columnSpan(1),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}

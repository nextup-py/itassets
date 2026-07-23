<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Información general ──────────────────────────────────────
                Section::make('Información general')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('asset_tag')
                            ->label('Código')
                            ->placeholder('Se genera automáticamente')
                            ->maxLength(20)
                            ->unique(Asset::class, 'asset_tag', ignoreRecord: true)
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Nombre / Descripción')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('asset_category_id')
                            ->label('Categoría')
                            ->required()
                            ->options(AssetCategory::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Estado')
                            ->required()
                            ->options(Asset::STATUSES)
                            ->default('stock')
                            ->columnSpan(1),

                        Select::make('condition')
                            ->label('Condición')
                            ->options(Asset::CONDITIONS)
                            ->columnSpan(1),

                        TextInput::make('brand')
                            ->label('Marca')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('model')
                            ->label('Modelo')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('serial_number')
                            ->label('Número de serie')
                            ->maxLength(100)
                            ->columnSpan(1),

                        FileUpload::make('photo')
                            ->label('Fotografía')
                            ->image()
                            ->disk('public')
                            ->directory('assets/photos')
                            ->maxSize(3072)
                            ->imagePreviewHeight('160')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                // ── Adquisición ───────────────────────────────────────────────
                Section::make('Adquisición')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        DatePicker::make('purchase_date')
                            ->label('Fecha de compra')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        TextInput::make('purchase_price')
                            ->label('Precio de compra')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->columnSpan(1),

                        Select::make('supplier_id')
                            ->label('Proveedor de compra')
                            ->options(Supplier::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Select::make('location_id')
                            ->label('Ubicación')
                            ->options(Location::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                // ── Garantía ──────────────────────────────────────────────────
                Section::make('Garantía')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        DatePicker::make('warranty_expiry_date')
                            ->label('Fecha de vencimiento de garantía')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        Select::make('warranty_supplier_id')
                            ->label('Proveedor de garantía')
                            ->options(Supplier::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

            ]);
    }
}

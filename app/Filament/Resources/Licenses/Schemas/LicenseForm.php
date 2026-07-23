<?php

namespace App\Filament\Resources\Licenses\Schemas;

use App\Models\License;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la licencia')
                    ->schema([
                        TextInput::make('product_name')
                            ->label('Producto / Software')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('license_type')
                            ->label('Tipo de licencia')
                            ->required()
                            ->options(License::TYPES)
                            ->columnSpan(1),

                        TextInput::make('total_seats')
                            ->label('Total de seats')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->columnSpan(1),

                        TextInput::make('license_key')
                            ->label('Clave / Número de licencia')
                            ->maxLength(255)
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Adquisición y vigencia')
                    ->schema([
                        DatePicker::make('purchase_date')
                            ->label('Fecha de compra')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        DatePicker::make('expiry_date')
                            ->label('Fecha de vencimiento')
                            ->displayFormat('d/m/Y')
                            ->after('purchase_date')
                            ->columnSpan(1),

                        TextInput::make('purchase_price')
                            ->label('Precio de compra')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->columnSpan(1),

                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->options(Supplier::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Notas')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}

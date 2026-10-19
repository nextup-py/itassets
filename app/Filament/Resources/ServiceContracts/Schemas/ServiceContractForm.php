<?php

namespace App\Filament\Resources\ServiceContracts\Schemas;

use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Models\ServiceContract;
use App\Support\Currencies;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información del contrato')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del contrato')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('contract_type')
                            ->label('Tipo')
                            ->required()
                            ->options(ServiceContract::TYPES)
                            ->columnSpan(1),

                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->required()
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => SupplierForm::configure($schema))
                            ->columnSpan(1),

                        TextInput::make('sla')
                            ->label('SLA / Nivel de servicio')
                            ->maxLength(255)
                            ->placeholder('Ej. Respuesta en 4 horas hábiles')
                            ->columnSpan(1),

                        Select::make('assets')
                            ->label('Activos cubiertos')
                            ->relationship('assets', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Opcional — dejar en blanco si el contrato no está atado a activos puntuales (ej. soporte general de red).')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Vigencia y facturación')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Fecha de inicio')
                            ->default(now())
                            ->displayFormat(current_date_format())
                            ->columnSpan(1),

                        DatePicker::make('expiry_date')
                            ->label('Fecha de vencimiento')
                            ->required()
                            ->after('start_date')
                            ->displayFormat(current_date_format())
                            ->columnSpan(1),

                        Toggle::make('auto_renew')
                            ->label('Renovación automática')
                            ->columnSpan(1),

                        Select::make('billing_cycle')
                            ->label('Periodicidad de facturación')
                            ->options(ServiceContract::BILLING_CYCLES)
                            ->columnSpan(1),

                        TextInput::make('cost')
                            ->label('Costo')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->helperText('Se interpreta en la moneda seleccionada abajo.')
                            ->columnSpan(1),

                        Select::make('currency')
                            ->label('Moneda')
                            ->helperText('En blanco = moneda base de la instalación.')
                            ->options(Currencies::LIST)
                            ->searchable()
                            ->columnSpan(1),
                    ])
                    ->columns(3),
            ]);
    }
}

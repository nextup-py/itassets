<?php

namespace App\Filament\Resources\MaintenanceRecords\Schemas;

use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Models\MaintenanceRecord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MaintenanceRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información del mantenimiento')
                    ->schema([
                        Select::make('asset_id')
                            ->label('Activo')
                            ->required()
                            ->relationship('asset', 'asset_tag')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->asset_tag}] {$record->name}")
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),

                        Select::make('type')
                            ->label('Tipo')
                            ->required()
                            ->options(MaintenanceRecord::TYPES)
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Estado')
                            ->required()
                            ->options(MaintenanceRecord::STATUSES)
                            ->default('pending')
                            ->live()
                            ->helperText('Al marcar como Completado, deberá indicar el nuevo estado del activo.')
                            ->columnSpan(1),

                        Select::make('new_asset_status')
                            ->label('Nuevo estado del activo')
                            ->options([
                                'available' => 'Disponible',
                                'retired' => 'Dado de baja',
                                'lost' => 'Perdido / Robado',
                            ])
                            ->default('available')
                            ->visible(fn (Get $get): bool => $get('status') === 'completed')
                            ->required(fn (Get $get): bool => $get('status') === 'completed')
                            ->helperText('Este será el nuevo estado del activo al completar el mantenimiento.')
                            ->columnSpan(1),

                        Textarea::make('description')
                            ->label('Descripción del problema / motivo')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Servicio')
                    ->schema([
                        TextInput::make('technician')
                            ->label('Técnico responsable')
                            ->maxLength(150)
                            ->columnSpan(1),

                        Select::make('supplier_id')
                            ->label('Proveedor de servicio')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => SupplierForm::configure($schema))
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Costo y fechas')
                    ->schema([
                        TextInput::make('cost')
                            ->label('Costo')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->columnSpan(1),

                        DatePicker::make('started_at')
                            ->label('Fecha de inicio')
                            ->required()
                            ->default(now())
                            ->displayFormat(current_date_format())
                            ->columnSpan(1),

                        DatePicker::make('completed_at')
                            ->label('Fecha de término')
                            ->displayFormat(current_date_format())
                            ->after('started_at')
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Section::make('Resolución')
                    ->schema([
                        Textarea::make('resolution')
                            ->label('Diagnóstico / Resolución')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notas adicionales')
                            ->rows(2)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

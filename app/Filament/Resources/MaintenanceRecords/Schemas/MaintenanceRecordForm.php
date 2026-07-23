<?php

namespace App\Filament\Resources\MaintenanceRecords\Schemas;

use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del mantenimiento')
                    ->schema([
                        Select::make('asset_id')
                            ->label('Activo')
                            ->required()
                            ->options(
                                Asset::orderBy('asset_tag')
                                    ->get()
                                    ->mapWithKeys(fn ($a) => [$a->id => "[{$a->asset_tag}] {$a->name}"])
                            )
                            ->searchable()
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
                            ->options(Supplier::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

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
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        DatePicker::make('completed_at')
                            ->label('Fecha de término')
                            ->displayFormat('d/m/Y')
                            ->after('started_at')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

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

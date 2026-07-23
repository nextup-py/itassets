<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Models\Asset;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Empleado')
                    ->required()
                    ->options(
                        Employee::where('status', 'active')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->columnSpan(1),

                DatePicker::make('assigned_at')
                    ->label('Fecha de asignación')
                    ->required()
                    ->default(now())
                    ->displayFormat('d/m/Y')
                    ->columnSpan(1),

                DatePicker::make('returned_at')
                    ->label('Fecha de devolución')
                    ->displayFormat('d/m/Y')
                    ->after('assigned_at')
                    ->columnSpan(1),

                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Repeater::make('assets')
                    ->label('Activos asignados')
                    ->schema([
                        Select::make('asset_id')
                            ->label('Activo')
                            ->required()
                            ->options(fn (): array => Asset::whereIn('status', ['available', 'stock'])
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => "[{$a->asset_tag}] {$a->name} ({$a->model})"])
                                ->toArray())
                            ->searchable()
                            ->columnSpan(3),

                        TextInput::make('charger_serial')
                            ->label('Cargador N/S')
                            ->maxLength(100)
                            ->columnSpan(2),

                        TextInput::make('ticket_number')
                            ->label('N.º Ticket')
                            ->maxLength(100)
                            ->columnSpan(2),
                    ])
                    ->columns(7)
                    ->addActionLabel('Agregar activo')
                    ->defaultItems(1)
                    ->minItems(1)
                    ->reorderable(false),
            ])
            ->columns(2);
    }
}

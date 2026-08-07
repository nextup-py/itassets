<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información personal')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Juan Pérez')
                            ->columnSpanFull(),

                        TextInput::make('legajo')
                            ->label('Legajo')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ej: AMP-001')
                            ->helperText('Identificador interno único del empleado.')
                            ->columnSpan(1),

                        TextInput::make('document_number')
                            ->label('Documento de identidad')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ej: 12.345.678')
                            ->helperText('DNI, pasaporte u otro documento de identidad.')
                            ->columnSpan(1),

                        Select::make('document_type')
                            ->label('Tipo de documento')
                            ->options(Employee::DOCUMENT_TYPES)
                            ->default('ci')
                            ->native(false)
                            ->placeholder('Seleccionar...')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Datos laborales')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->hiddenOn('create')
                            ->columnSpan(1),

                        Select::make('department_id')
                            ->label('Departamento')
                            ->relationship('department', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        TextInput::make('position')
                            ->label('Cargo')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ej: Desarrollador Backend')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Contacto')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('nombre@empresa.com')
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('+54 11 5555-1234')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }
}

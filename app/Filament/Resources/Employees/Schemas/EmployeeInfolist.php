<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Employee;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identidad')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre completo')
                            ->columnSpan(2),

                        TextEntry::make('legajo')
                            ->label('Legajo'),

                        TextEntry::make('document_number')
                            ->label('Documento de identidad'),

                        TextEntry::make('document_type')
                            ->label('Tipo de documento')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? (Employee::DOCUMENT_TYPES[$state] ?? $state) : null)
                            ->placeholder('—'),

                        IconEntry::make('is_active')
                            ->label('Activo')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Empleo')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        TextEntry::make('department.name')
                            ->label('Departamento')
                            ->placeholder('—'),

                        TextEntry::make('position')
                            ->label('Cargo')
                            ->placeholder('—'),

                        TextEntry::make('active_assignments_count')
                            ->label('Asignaciones activas')
                            ->state(fn (Employee $record) => $record->activeAssignments()->count())
                            ->badge(),

                        TextEntry::make('license_assignments_count')
                            ->label('Licencias asignadas')
                            ->state(fn (Employee $record) => $record->licenseAssignments()->count())
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Contacto')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        TextEntry::make('email')
                            ->label('Correo electrónico')
                            ->placeholder('—')
                            ->copyable(),

                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
            ]);
    }
}

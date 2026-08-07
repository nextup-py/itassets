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
            ->components([
                Section::make('Información general')
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

                        TextEntry::make('department.name')
                            ->label('Departamento')
                            ->placeholder('—'),

                        TextEntry::make('position')
                            ->label('Cargo')
                            ->placeholder('—'),

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

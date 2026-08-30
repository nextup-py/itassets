<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información general')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),

                        TextEntry::make('employees_count')
                            ->label('Empleados')
                            ->state(fn ($record) => $record->employees()->count())
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Sistema')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado el')
                            ->dateTime(current_datetime_format()),

                        TextEntry::make('updated_at')
                            ->label('Actualizado el')
                            ->dateTime(current_datetime_format()),
                    ])
                    ->columns(2),
            ]);
    }
}

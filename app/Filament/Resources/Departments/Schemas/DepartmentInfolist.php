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
            ->components([
                Section::make('Información general')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->columnSpan(1),

                        TextEntry::make('created_at')
                            ->label('Creado el')
                            ->dateTime(current_datetime_format())
                            ->columnSpan(1),

                        TextEntry::make('updated_at')
                            ->label('Actualizado el')
                            ->dateTime(current_datetime_format())
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }
}

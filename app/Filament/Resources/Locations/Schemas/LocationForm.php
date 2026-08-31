<?php

namespace App\Filament\Resources\Locations\Schemas;

use App\Models\Location;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->unique(Location::class, 'name', ignoreRecord: true)
                    ->columnSpanFull(),

                TextInput::make('building')
                    ->label('Edificio')
                    ->maxLength(100)
                    ->columnSpan(1),

                TextInput::make('floor')
                    ->label('Piso')
                    ->maxLength(20)
                    ->columnSpan(1),

                TextInput::make('room')
                    ->label('Sala / Área')
                    ->maxLength(100)
                    ->columnSpan(1),

                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }
}

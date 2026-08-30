<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información general')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->columnSpanFull(),

                        TextEntry::make('building')
                            ->label('Edificio')
                            ->placeholder('—'),

                        TextEntry::make('floor')
                            ->label('Piso')
                            ->placeholder('—'),

                        TextEntry::make('room')
                            ->label('Sala / Área')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Notas')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->placeholder('Sin notas')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

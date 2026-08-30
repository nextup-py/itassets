<?php

namespace App\Filament\Resources\Supplies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información general')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),

                        TextEntry::make('category.name')
                            ->label('Categoría')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Existencias')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                        TextEntry::make('quantity_available')
                            ->label('Disponible')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('quantity_damaged')
                            ->label('Dañado / En revisión')
                            ->badge()
                            ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),

                        TextEntry::make('total')
                            ->label('Total')
                            ->state(fn ($record) => $record->quantity_available + $record->quantity_damaged)
                            ->badge()
                            ->color('gray'),
                    ])
                    ->columns(3),

                Section::make('Origen')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        TextEntry::make('supplier.name')
                            ->label('Proveedor')
                            ->placeholder('—'),

                        TextEntry::make('location.name')
                            ->label('Ubicación')
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

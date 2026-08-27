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
            ->components([
                Section::make('Información general')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),

                        TextEntry::make('category.name')
                            ->label('Categoría')
                            ->placeholder('—'),

                        TextEntry::make('quantity_available')
                            ->label('Disponible')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('quantity_damaged')
                            ->label('Dañado / En revisión')
                            ->badge()
                            ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),

                        TextEntry::make('supplier.name')
                            ->label('Proveedor')
                            ->placeholder('—'),

                        TextEntry::make('location.name')
                            ->label('Ubicación')
                            ->placeholder('—'),

                        TextEntry::make('notes')
                            ->label('Notas')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}

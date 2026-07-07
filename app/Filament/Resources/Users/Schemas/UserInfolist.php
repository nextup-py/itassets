<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del usuario')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->columnSpan(1),

                        TextEntry::make('email')
                            ->label('Correo electrónico')
                            ->copyable()
                            ->columnSpan(1),

                        TextEntry::make('roles')
                            ->label('Roles')
                            ->badge()
                            ->color('primary')
                            ->formatStateUsing(fn ($state) => $state->pluck('name')->implode(', '))
                            ->visible(fn ($record) => $record->roles->isNotEmpty())
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Información del sistema')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado el')
                            ->dateTime('d/m/Y H:i')
                            ->columnSpan(1),

                        TextEntry::make('updated_at')
                            ->label('Actualizado el')
                            ->dateTime('d/m/Y H:i')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }
}

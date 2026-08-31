<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-envelope')
                    ->columnSpan(1),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                    ->minLength(8)
                    ->same('password_confirmation')
                    ->dehydrated(fn ($state) => filled($state))
                    ->placeholder('Dejar vacío para mantener actual')
                    ->helperText('Mínimo 8 caracteres.')
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('password_confirmation')
                    ->label('Confirmar contraseña')
                    ->password()
                    ->revealable()
                    ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                    ->dehydrated(false)
                    ->columnSpan(1),

                Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->relationship(
                        'roles',
                        'name',
                        modifyQueryUsing: fn (Builder $query) => auth()->user()->hasRole('Admin')
                            ? $query
                            : $query->where('name', '!=', 'Admin'),
                    )
                    ->preload()
                    ->required()
                    ->helperText(fn () => auth()->user()->hasRole('Admin') ? null : 'Solo un Admin puede asignar el rol Admin.')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}

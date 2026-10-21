<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->unique(Department::class, 'name', ignoreRecord: true)
                    ->columnSpanFull(),

                Select::make('contact_employee_id')
                    ->label('Responsable de contacto')
                    ->relationship(
                        name: 'contactEmployee',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('is_active', true),
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('Recibe el documento de asignación cuando un activo se asigna a este departamento/área en lugar de a un empleado.')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}

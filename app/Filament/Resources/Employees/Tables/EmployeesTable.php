<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('legajo')
                    ->label('Legajo')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('document_number')
                    ->label('Documento de identidad')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('document_type')
                    ->label('Tipo de documento')
                    ->formatStateUsing(fn (?string $state): ?string => $state ? (Employee::DOCUMENT_TYPES[$state] ?? $state) : null)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department.name')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('position')
                    ->label('Cargo')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date(current_date_format())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Activo'),

                SelectFilter::make('department_id')
                    ->label('Departamento')
                    ->relationship('department', 'name'),

                SelectFilter::make('document_type')
                    ->label('Tipo de documento')
                    ->options(Employee::DOCUMENT_TYPES),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}

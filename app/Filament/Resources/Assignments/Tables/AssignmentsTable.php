<?php

namespace App\Filament\Resources\Assignments\Tables;

use App\Models\Assignment;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Empleado')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.department')
                    ->label('Departamento')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('asset_list')
                    ->label('Activos')
                    ->html()
                    ->getStateUsing(fn (Assignment $record): string => $record->assets
                        ->map(fn ($a) => e('[' . $a->asset_tag . '] ' . $a->name))
                        ->implode('<br>')),

                TextColumn::make('assigned_at')
                    ->label('Asignado el')
                    ->date(current_date_format())
                    ->sortable(),

                TextColumn::make('returned_at')
                    ->label('Devuelto el')
                    ->date(current_date_format())
                    ->placeholder('Activo')
                    ->sortable(),

                TextColumn::make('assigned_by')
                    ->label('Asignado por')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Solo activos (sin devolver)')
                    ->query(fn (Builder $query) => $query->active())
                    ->toggle(),

                SelectFilter::make('employee_id')
                    ->label('Empleado')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Assignment $record) => route('assignments.pdf', $record), shouldOpenInNewTab: true),
                Action::make('returnNow')
                    ->label('Devolver ahora')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->authorize('update_assignment')
                    ->visible(fn (Assignment $record) => $record->isActive())
                    ->requiresConfirmation()
                    ->action(fn (Assignment $record) => $record->update(['returned_at' => now()])),
            ])
            ->defaultSort('assigned_at', 'desc');
    }
}

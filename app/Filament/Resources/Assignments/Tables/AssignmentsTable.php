<?php

namespace App\Filament\Resources\Assignments\Tables;

use App\Models\Assignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('returned_at')
                    ->label('Devuelto el')
                    ->date('d/m/Y')
                    ->placeholder('Activo')
                    ->sortable(),

                TextColumn::make('assigned_by')
                    ->label('Asignado por')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Solo activos (sin devolver)')
                    ->query(fn (Builder $query) => $query->active())
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Assignment $record) => route('assignments.pdf', $record), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('assigned_at', 'desc');
    }
}

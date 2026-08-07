<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Filament\Concerns\HasRelationManagerPermissions;
use App\Models\Assignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentsRelationManager extends RelationManager
{
    use HasRelationManagerPermissions;

    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Activos asignados';

    protected function getPermissionName(): string
    {
        return 'assignment';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('asset_list')
                    ->label('Activos')
                    ->getStateUsing(fn (Assignment $record): string => $record->assets
                        ->map(fn ($a) => '[' . $a->asset_tag . '] ' . $a->name)
                        ->implode("\n"))
                    ->searchable(),

                TextColumn::make('assigned_at')
                    ->label('Asignado el')
                    ->date(current_date_format())
                    ->sortable(),

                TextColumn::make('returned_at')
                    ->label('Devuelto el')
                    ->date(current_date_format())
                    ->placeholder('Activo')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Solo activos (sin devolver)')
                    ->query(fn (Builder $q) => $q->whereNull('returned_at'))
                    ->toggle()
                    ->default(),
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Assignment $record) => route('assignments.pdf', $record), shouldOpenInNewTab: true),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('assigned_at', 'desc');
    }
}

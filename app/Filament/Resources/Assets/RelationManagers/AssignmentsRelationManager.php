<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use App\Filament\Concerns\HasRelationManagerPermissions;
use App\Filament\Resources\Assignments\AssignmentResource;
use App\Models\Assignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

    protected static ?string $title = 'Historial de asignaciones';

    protected function getPermissionName(): string
    {
        return 'assignment';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Empleado')
                    ->required()
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?Assignment $record) => $query
                            ->where('is_active', true)
                            ->when($record, fn (Builder $q) => $q->orWhere('id', $record->employee_id)),
                    )
                    ->searchable()
                    ->preload()
                    ->columnSpan(1),

                DatePicker::make('assigned_at')
                    ->label('Fecha de asignación')
                    ->required()
                    ->default(now())
                    ->displayFormat(current_date_format())
                    ->columnSpan(1),

                TextInput::make('charger_serial')
                    ->label('Cargador N/S')
                    ->maxLength(100)
                    ->columnSpan(1),

                TextInput::make('ticket_number')
                    ->label('N.º Ticket')
                    ->maxLength(100)
                    ->columnSpan(1),

                DatePicker::make('returned_at')
                    ->label('Fecha de devolución')
                    ->displayFormat(current_date_format())
                    ->after('assigned_at')
                    ->columnSpan(1),

                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Empleado')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.department.name')
                    ->label('Departamento')
                    ->placeholder('—'),

                TextColumn::make('assigned_at')
                    ->label('Asignado el')
                    ->date(current_date_format())
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('assignments.assigned_at', $direction)),

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
                    ->label('Solo asignaciones activas')
                    ->query(fn (Builder $q) => $q->whereNull('returned_at'))
                    ->toggle(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nueva asignación')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['assigned_by'] = auth()->user()?->name;
                        return $data;
                    })
                    ->after(function (Assignment $record, array $data): void {
                        $record->assets()->attach($this->getOwnerRecord()->id, [
                            'charger_serial' => $data['charger_serial'] ?? null,
                            'ticket_number'  => $data['ticket_number'] ?? null,
                            'assigned_at'    => $data['assigned_at'],
                            'notes'          => $data['notes'] ?? null,
                        ]);
                    }),
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Assignment $record) => route('assignments.pdf', $record), shouldOpenInNewTab: true),
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Assignment $record) => AssignmentResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('assignments.assigned_at', 'desc');
    }
}

<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use App\Filament\Concerns\HasRelationManagerPermissions;
use App\Models\Assignment;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                    ->options(
                        Employee::where('status', 'active')->orderBy('name')->pluck('name', 'id')
                    )
                    ->searchable()
                    ->columnSpan(1),

                DatePicker::make('assigned_at')
                    ->label('Fecha de asignación')
                    ->required()
                    ->default(now())
                    ->displayFormat('d/m/Y')
                    ->columnSpan(1),

                TextInput::make('charger_serial')
                    ->label('Cargador SN')
                    ->maxLength(100)
                    ->columnSpan(1),

                TextInput::make('ticket_number')
                    ->label('N.º Ticket')
                    ->maxLength(100)
                    ->columnSpan(1),

                DatePicker::make('returned_at')
                    ->label('Fecha de devolución')
                    ->displayFormat('d/m/Y')
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

                TextColumn::make('employee.department')
                    ->label('Departamento')
                    ->placeholder('—'),

                TextColumn::make('assigned_at')
                    ->label('Asignado el')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('returned_at')
                    ->label('Devuelto el')
                    ->date('d/m/Y')
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

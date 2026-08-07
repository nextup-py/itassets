<?php

namespace App\Filament\Resources\Licenses\RelationManagers;

use App\Filament\Concerns\HasRelationManagerPermissions;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\LicenseAssignment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

    protected static ?string $title = 'Asignaciones de esta licencia';

    protected function getPermissionName(): string
    {
        return 'assignment';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('asset_id')
                    ->label('Activo (opcional)')
                    ->options(
                        Asset::orderBy('asset_tag')
                            ->get()
                            ->mapWithKeys(fn ($a) => [$a->id => "[{$a->asset_tag}] {$a->name}"])
                    )
                    ->searchable()
                    ->nullable()
                    ->columnSpan(1),

                Select::make('employee_id')
                    ->label('Empleado (opcional)')
                    ->options(function (?LicenseAssignment $record): array {
                        return Employee::query()
                            ->where(function ($query) use ($record) {
                                $query->where('is_active', true);

                                if ($record?->employee_id) {
                                    $query->orWhere('id', $record->employee_id);
                                }
                            })
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->nullable()
                    ->columnSpan(1),

                DatePicker::make('assigned_at')
                    ->label('Fecha de asignación')
                    ->required()
                    ->default(now())
                    ->displayFormat(current_date_format())
                    ->columnSpan(1),

                DatePicker::make('released_at')
                    ->label('Fecha de liberación')
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
                TextColumn::make('asset.asset_tag')
                    ->label('Activo (código)')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('asset.name')
                    ->label('Activo')
                    ->placeholder('—')
                    ->limit(30),

                TextColumn::make('employee.name')
                    ->label('Empleado')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('assigned_at')
                    ->label('Asignado el')
                    ->date(current_date_format())
                    ->sortable(),

                TextColumn::make('released_at')
                    ->label('Liberado el')
                    ->date(current_date_format())
                    ->placeholder('Activo')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Solo activas (sin liberar)')
                    ->query(fn (Builder $q) => $q->whereNull('released_at'))
                    ->toggle()
                    ->default(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Asignar licencia')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (blank($data['asset_id'] ?? null) && blank($data['employee_id'] ?? null)) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'asset_id' => 'Debe seleccionar al menos un Activo o un Empleado.',
                            ]);
                        }
                        return $data;
                    }),
            ])
            ->recordActions([
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

<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Models\Asset;
use App\Models\Assignment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
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
                    ->disabled(fn (?Assignment $record): bool => filled($record?->returned_at))
                    ->helperText(fn (?Assignment $record): string => filled($record?->returned_at)
                        ? 'No se puede modificar: la asignación ya fue devuelta.'
                        : 'Solo se muestran empleados activos.')
                    ->columnSpan(1),

                DatePicker::make('assigned_at')
                    ->label('Fecha de asignación')
                    ->required()
                    ->default(now())
                    ->displayFormat(current_date_format())
                    ->disabled(fn (?Assignment $record): bool => filled($record?->returned_at))
                    ->helperText(fn (?Assignment $record): ?string => filled($record?->returned_at)
                        ? 'No se puede modificar: la asignación ya fue devuelta.'
                        : null)
                    ->columnSpan(1),

                DatePicker::make('returned_at')
                    ->label('Fecha de devolución')
                    ->displayFormat(current_date_format())
                    ->after('assigned_at')
                    ->helperText('Dejar vacío si el activo sigue asignado.')
                    ->columnSpan(1),

                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Repeater::make('assets')
                    ->label('Activos asignados')
                    ->schema([
                        Select::make('asset_id')
                            ->label('Activo')
                            ->required()
                            ->options(function (?Assignment $record): array {
                                return Asset::query()
                                    ->where(function ($query) use ($record) {
                                        $query->whereIn('status', ['available', 'stock']);

                                        if ($record) {
                                            $query->orWhereIn('id', $record->assets()->pluck('assets.id'));
                                        }
                                    })
                                    ->get()
                                    ->mapWithKeys(fn ($a) => [$a->id => "[{$a->asset_tag}] {$a->name} ({$a->model})"])
                                    ->toArray();
                            })
                            ->searchable()
                            ->helperText('Solo se muestran activos disponibles o en stock.')
                            ->columnSpan(3),

                        TextInput::make('charger_serial')
                            ->label('Cargador N/S')
                            ->maxLength(100)
                            ->columnSpan(2),

                        TextInput::make('ticket_number')
                            ->label('N.º Ticket')
                            ->maxLength(100)
                            ->columnSpan(2),
                    ])
                    ->columns(7)
                    ->addActionLabel('Agregar activo')
                    ->defaultItems(1)
                    ->minItems(1)
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }
}

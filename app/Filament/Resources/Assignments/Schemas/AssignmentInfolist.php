<?php

namespace App\Filament\Resources\Assignments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssignmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asignación')
                    ->icon('heroicon-o-arrows-right-left')
                    ->schema([
                        TextEntry::make('employee.name')
                            ->label('Empleado'),

                        TextEntry::make('assigned_by')
                            ->label('Asignado por')
                            ->placeholder('—'),

                        TextEntry::make('assigned_at')
                            ->label('Asignado el')
                            ->date(current_date_format()),

                        TextEntry::make('returned_at')
                            ->label('Devuelto el')
                            ->date(current_date_format())
                            ->placeholder('Aún no devuelto')
                            ->color(fn ($state) => is_null($state) ? 'success' : null),
                    ])
                    ->columns(2),

                Section::make('Activos asignados')
                    ->icon('heroicon-o-computer-desktop')
                    ->schema([
                        TextEntry::make('asset_list')
                            ->label('')
                            ->html()
                            ->getStateUsing(fn ($record): string => $record->assets
                                ->map(fn ($a) => sprintf(
                                    '<strong>[%s] %s</strong>%s',
                                    e($a->asset_tag),
                                    e($a->name),
                                    $a->pivot->charger_serial ? '<br><small>Cargador N/S: ' . e($a->pivot->charger_serial) . '</small>' : ''
                                ))
                                ->implode('<hr class="my-1">')),
                    ]),

                Section::make('Notas')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->placeholder('Sin notas')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

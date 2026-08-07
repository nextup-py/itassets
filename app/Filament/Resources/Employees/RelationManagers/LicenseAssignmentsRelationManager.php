<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Filament\Resources\Licenses\LicenseResource;
use App\Models\License;
use App\Models\LicenseAssignment;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LicenseAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'licenseAssignments';

    protected static ?string $title = 'Licencias asignadas';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('license.product_name')
                    ->label('Producto / Software')
                    ->searchable(),

                TextColumn::make('license.license_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => License::TYPES[$state] ?? $state ?? '—'),

                TextColumn::make('assigned_at')
                    ->label('Asignada el')
                    ->date(current_date_format())
                    ->sortable(),

                TextColumn::make('released_at')
                    ->label('Liberada el')
                    ->date(current_date_format())
                    ->placeholder('Activa')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Solo activas (sin liberar)')
                    ->query(fn (Builder $q) => $q->whereNull('released_at'))
                    ->toggle()
                    ->default(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ver licencia')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (LicenseAssignment $record) => LicenseResource::getUrl('view', ['record' => $record->license_id])),
            ])
            ->defaultSort('assigned_at', 'desc');
    }
}

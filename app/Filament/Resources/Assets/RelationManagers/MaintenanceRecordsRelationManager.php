<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use App\Filament\Concerns\HasRelationManagerPermissions;
use App\Models\MaintenanceRecord;
use App\Models\Supplier;
use App\Services\MaintenanceService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceRecordsRelationManager extends RelationManager
{
    use HasRelationManagerPermissions;

    protected static string $relationship = 'maintenanceRecords';

    protected static ?string $title = 'Historial de mantenimientos';

    protected function getPermissionName(): string
    {
        return 'maintenance_record';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo')
                    ->required()
                    ->options(MaintenanceRecord::TYPES)
                    ->columnSpan(1),

                Select::make('status')
                    ->label('Estado')
                    ->required()
                    ->options(MaintenanceRecord::STATUSES)
                    ->default('pending')
                    ->live()
                    ->columnSpan(1),

                Select::make('new_asset_status')
                    ->label('Nuevo estado del activo')
                    ->options([
                        'available' => 'Disponible',
                        'retired' => 'Dado de baja',
                        'lost' => 'Perdido / Robado',
                    ])
                    ->default('available')
                    ->visible(fn (Get $get): bool => $get('status') === 'completed')
                    ->required(fn (Get $get): bool => $get('status') === 'completed')
                    ->columnSpan(1),

                Textarea::make('description')
                    ->label('Descripción / Motivo')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                TextInput::make('technician')
                    ->label('Técnico')
                    ->maxLength(150)
                    ->columnSpan(1),

                Select::make('supplier_id')
                    ->label('Proveedor')
                    ->options(Supplier::pluck('name', 'id'))
                    ->searchable()
                    ->columnSpan(1),

                TextInput::make('cost')
                    ->label('Costo')
                    ->numeric()
                    ->prefix('$')
                    ->columnSpan(1),

                DatePicker::make('started_at')
                    ->label('Fecha de inicio')
                    ->required()
                    ->default(now())
                    ->displayFormat(current_date_format())
                    ->columnSpan(1),

                DatePicker::make('completed_at')
                    ->label('Fecha de término')
                    ->displayFormat(current_date_format())
                    ->after('started_at')
                    ->columnSpan(1),

                Textarea::make('resolution')
                    ->label('Resolución / Diagnóstico')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Notas adicionales')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (MaintenanceRecord $record): string => $record->getTypeLabel()),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (MaintenanceRecord $record): string => $record->getStatusLabel())
                    ->color(fn (MaintenanceRecord $record): string => $record->getStatusBadgeColor()),

                TextColumn::make('technician')
                    ->label('Técnico')
                    ->placeholder('—'),

                TextColumn::make('cost')
                    ->label('Costo')
                    ->formatStateUsing(fn ($state) => is_null($state) ? '—' : \format_currency($state)),

                TextColumn::make('started_at')
                    ->label('Inicio')
                    ->date(current_date_format())
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Término')
                    ->date(current_date_format())
                    ->placeholder('En curso'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(MaintenanceRecord::STATUSES),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nuevo mantenimiento'),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn (MaintenanceRecord $record, array $data) => app(MaintenanceService::class)->syncAssetStatus($record, $data['new_asset_status'] ?? null)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}

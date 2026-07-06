<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Assets\AssetResource;
use App\Models\Asset;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ActiveMaintenanceWidget extends TableWidget
{
    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 6;

    protected ?string $pollingInterval = '60s';

    protected function getTableHeading(): string
    {
        return 'Activos en mantenimiento';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Asset::where('status', 'maintenance')
                    ->orderBy('updated_at', 'desc')
            )
            ->columns([
                TextColumn::make('asset_tag')
                    ->label('Código')
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Activo')
                    ->limit(25),

                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->since(),

                TextColumn::make('maintenance_records_count')
                    ->label('Mantenimientos')
                    ->counts('maintenanceRecords'),
            ])
            ->recordUrl(fn (Asset $record): string => AssetResource::getUrl('view', ['record' => $record]))
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}

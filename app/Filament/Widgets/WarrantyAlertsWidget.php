<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Assets\AssetResource;
use App\Models\Asset;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class WarrantyAlertsWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '300s';

    protected function getTableHeading(): string
    {
        return 'Garantías próximas a vencer (60 días)';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Asset::whereNotNull('warranty_expiry_date')
                    ->where(function ($q) {
                        $q->whereBetween('warranty_expiry_date', [now(), now()->addDays(60)])
                          ->orWhere('warranty_expiry_date', '<', now());
                    })
                    ->orderBy('warranty_expiry_date')
            )
            ->columns([
                TextColumn::make('asset_tag')
                    ->label('Código')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Activo')
                    ->limit(30),

                TextColumn::make('warranty_expiry_date')
                    ->label('Vence el')
                    ->date('d/m/Y')
                    ->color(fn ($record) => $record->warranty_expiry_date->isPast() ? 'danger' : 'warning'),

                TextColumn::make('warrantySupplier.name')
                    ->label('Proveedor de garantía')
                    ->placeholder('—'),
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
